<?php

namespace App\Http\Controllers\Resource;

use App\Provider;
use App\User;
use App\UserRequests;
use Carbon\Carbon;
use function currency;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Storage;
use Setting;
use QrCode;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\json_decode;
use App\Admin;

class UserResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store', 'update', 'destroy']]);

        $this->middleware('permission:user-list', ['only' => ['index']]);
        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);

        $this->perpage = config('constants.per_page', '10');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (!empty($request->download) && $request->download == 'all') {
            $users = User::where('user_type', '<>', 'COMPANY')->orderBy('id', 'asc')->get();
            return response()->json(array('success' => true, 'data' => $users));
        }
        // else {
        //     $users = User::where('user_type', '<>', 'COMPANY')->orderBy('created_at', 'desc')->paginate($this->perpage);
        //     $pagination = (new Helper)->formatPagination($users);
        //     return view('admin.users.index', compact('users', 'pagination'));
        // }

        if ($request->ajax())
        {
            // $params = $request->all();

            $start = $request->start;
            $length = $request->length;
            $search_value = $request->search['value'];
            $orders = ['id', 'first_name', 'last_name', 'email', 'mobile', 'rating', 'wallet_balance', 'id'];
            $order_name = $orders[intval($request->order[0]['column'])];
            // $order_name = $request->columns[intval($request->order[0]['column'])]['name'];
            $dir = $request->order[0]['dir'];
            $draw = $request->draw;

            $users = User::where('user_type', '<>', 'COMPANY')->where('fleet_id',0)
                ->where(function ($query) use ($search_value) {
                    $query->orWhere('id', 'like', '%' . $search_value . '%')
                        ->orWhere('first_name', 'like', '%' . $search_value . '%')
                        ->orWhere('last_name', 'like', '%' . $search_value . '%')
                        ->orWhere('email', 'like', '%' . $search_value . '%')
                        ->orWhere('mobile', 'like', '%' . $search_value . '%')
                        ->orWhere('wallet_balance', 'like', '%' . $search_value . '%')
                        ->orWhere('rating', 'like', '%' . $search_value . '%');
                })
                ->orderBy(empty($order_name) ? 'id' : $order_name, $dir)
                ->offset($start)
                ->limit($length)
                ->get();
            foreach($users as $key=>$user) {
                $user->wallet_balance = currency($user->wallet_balance);
                $users[$key] = $user;
            }
            $count = User::where('user_type', '<>', 'COMPANY')->where('fleet_id',0)
            ->where(function ($query) use ($search_value) {
                $query->orWhere('id', 'like', '%' . $search_value . '%')
                    ->orWhere('first_name', 'like', '%' . $search_value . '%')
                    ->orWhere('last_name', 'like', '%' . $search_value . '%')
                    ->orWhere('email', 'like', '%' . $search_value . '%')
                    ->orWhere('mobile', 'like', '%' . $search_value . '%')
                    ->orWhere('wallet_balance', 'like', '%' . $search_value . '%')
                    ->orWhere('rating', 'like', '%' . $search_value . '%');
            })
            ->orderBy(empty($order_name) ? 'id' : $order_name, $dir)
            ->count();

            $total = User::where('user_type', '<>', 'COMPANY')
            ->orderBy(empty($order_name) ? 'id' : $order_name, $dir)
            ->count();

            $result = array();
            $result['recordsTotal'] = $total;
            $result['recordsFiltered'] = $count;
            $result['data'] = $users;
            $result['draw'] = $draw;
            $result['aaaaa'] = $request->all();
            return response()->json($result);
            // return response()->json($request->all());

            // $resultssss = array();
            // $resultssss[0]["id"] = '1';
            // $resultssss[0]["first_name"] = '2';
            // $resultssss[0]["last_name"] = '3';
            // $resultssss[0]["email"] = '4';
            // $resultssss[0]["mobile"] = '5';
            // $resultssss[0]["rating"] = '6';
            // $resultssss[0]["wallet_balance"] = '7';
            // $resultssss[0]["asd"] = '8';
            // $result['recordsTotal'] = 1;
            // $result['recordsFiltered'] = 1;
            // $result['data'] = $resultssss;
            // $result['draw'] = $draw;

            // return response()->json($result);
        }
        return view('admin.users.index');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|unique:users,email|email|max:255',
            'country_code' => 'required|max:25',
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        try {

            $user = $request->all();

            $user['payment_mode'] = 'CASH';
            $user['password'] = bcrypt($request->password);
            if ($request->hasFile('picture')) {
                $user['picture'] = $request->picture->store('user/profile');
            }
            // QrCode generator
            $file = QrCode::format('png')->size(500)->margin(10)->generate('{
                "country_code":' . '"' . $request->country_code . '"' . ',
                "phone_number":' . '"' . $request->mobile . '"' . '
                }');
            // $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
            $user['qrcode_url'] = Helper::upload_qrCode($request->mobile, $file);

            $user = User::create($user);
            Helper::welcomeEmailToNewUser('user', User::find($user->id));
            return back()->with('flash_success', trans('admin.user_msgs.user_saved'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.user_msgs.user_not_found'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     *
     * @return Response
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.user-details', compact('user'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     *
     * @return Response
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.edit', compact('user'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'country_code' => 'required|max:25',
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $user = User::findOrFail($id);

            if ($request->hasFile('picture')) {
                Storage::delete($user->picture);
                $user->picture = $request->picture->store('user/profile');
            }
            // QrCode generator
            $file = QrCode::format('png')->size(500)->margin(10)->generate('{
                "country_code":' . '"' . $request->country_code . '"' . ',
                "phone_number":' . '"' . $request->mobile . '"' . '
                }');
            // $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
            $user->qrcode_url = Helper::upload_qrCode($request->mobile, $file);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->country_code = $request->country_code;
            $user->mobile = $request->mobile;
            $user->save();

            return redirect()->route('admin.user.index')->with('flash_success', trans('admin.user_msgs.user_update'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.user_msgs.user_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     *
     * @return Response
     */
    public function destroy($id)
    {

        try {

            User::find($id)->delete();
            return back()->with('message', trans('admin.user_msgs.user_delete'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.user_msgs.user_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Provider  $provider
     * @return Response
     */

	public function request($id)
	{
		try
		{
			$user_type = User::where('id',$id)->value('user_type');
			if($user_type === 'NORMAL'){
				$requests = UserRequests::where('user_requests.user_id', $id)
					->RequestHistory()
					->paginate($this->perpage);
			}elseif($user_type === 'COMPANY'){
				$user_ids  = User::where( 'company_id', $id )->select( 'id' )->get();
				$requests = UserRequests::whereIn('user_requests.user_id', $user_ids)
					->RequestHistory()
					->paginate($this->perpage);
			}

			$pagination = (new Helper)->formatPagination($requests);
			$trips = $requests;
			$admin = Admin::where('id', 1)->first();
			$dates['yesterday'] = Carbon::yesterday()->format('Y-m-d');
			$dates['today'] = Carbon::today()->format('Y-m-d');
			$dates['pre_week_start'] = Carbon::today()->subWeek()->format('Y-m-d');
			$dates['pre_week_end'] = Carbon::parse('last sunday of this month')->format('Y-m-d');
			$dates['cur_week_start'] = Carbon::today()->startOfWeek()->format('Y-m-d');
			$dates['cur_week_end'] = Carbon::today()->endOfWeek()->format('Y-m-d');
			$dates['pre_month_start'] = Carbon::parse('first day of last month')->format('Y-m-d');
			$dates['pre_month_end'] = Carbon::parse('last day of last month')->format('Y-m-d');
			$dates['cur_month_start'] = Carbon::parse('first day of this month')->format('Y-m-d');
			$dates['cur_month_end'] = Carbon::parse('last day of this month')->format('Y-m-d');
			$dates['pre_year_start'] = Carbon::parse('first day of last year')->format('Y-m-d');
			$dates['pre_year_end'] = Carbon::parse('last day of last year')->format('Y-m-d');
			$dates['cur_year_start'] = Carbon::parse('first day of this year')->format('Y-m-d');
			$dates['cur_year_end'] = Carbon::parse('last day of this year')->format('Y-m-d');
			$dates['nextWeek'] = Carbon::today()->addWeek()->format('Y-m-d');
			return view('admin.request.index', compact('requests', 'pagination', 'trips', 'admin','dates'));
		} catch (Exception $e) {
			return back()->with('flash_error', trans('admin.something_wrong'));
		}
    }
    
    
}

<?php

namespace App\Http\Controllers\Resource;

use App\Provider;
use App\User;
use App\UserRequests;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use Storage;
use Setting;
use QrCode;

class UserProResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store', 'update','destroy']]);

        $this->middleware('permission:user-list', ['only' => ['index']]);
        $this->middleware('permission:user-create', ['only' => ['create','store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
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


        if(!empty($request->page) && $request->page=='all'){
            $users = User::where('user_type', 'COMPANY')->orderBy('id' , 'asc')->get();
            return response()->json(array('success' => true, 'data'=>$users));
        }
        else{

            $users = User::where('user_type', 'COMPANY')->orderBy('created_at' , 'desc')->paginate($this->perpage);
            $pagination=(new Helper)->formatPagination($users);
            return view('admin.users-pro.index', compact('users','pagination'));
        }


    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.users-pro.create');
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
            'company_name' => 'required|max:255',
            'reg_number' => 'required|max:255',
            'company_address' => 'required|max:255',
            'company_zip_code' => 'required|max:255',
            'company_city' => 'required|max:255',
            'email' => 'required|unique:users,email|email|max:255',
            'country_code' => 'required|max:25',
            'mobile' => 'required|digits_between:6,13',
            'password' => 'required|min:6|confirmed',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try{

            if ($request->has('mobile')) {
                $u = User::where('mobile', $request->mobile)->get();
                if (count($u) > 0) {
                    return back()->with('flash_error', trans('This number are already exist'));
                }
            }

            $user = $request->all();

            $user['payment_mode'] = 'CASH';
            $user['password'] = bcrypt($request->password);
            if($request->hasFile('picture')) {
                $user['picture'] = $request->picture->store('user/profile');
            }
            $user['user_type'] = 'COMPANY';

            // QrCode generator
            $file=QrCode::format('png')->size(500)->margin(10)->generate('{
                "country_code":'.'"'.$request->country_code.'"'.',
                "phone_number":'.'"'.$request->mobile.'"'.'
            }');
            // $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
            $user['qrcode_url'] = Helper::upload_qrCode($request->mobile,$file);
            if (empty($user['wallet_limit']))   $user['wallet_limit'] = 0;
            $user['wallet_limit'] = $request->input('wallet_limit');

            $user = User::create($user);

			$users = User::findorFail($user->id);
			$users->company_id = $user->id;
			$users->company_zip_code = $user->company_zip_code;
			$users->company_city = $user->company_city;
			$users->company_address = $user->company_address;
			$users->update();

            Helper::welcomeEmailToNewUser('user-pro', User::find($user->id));

            return back()->with('flash_success', trans('admin.user_msgs.user_saved'));

        }

        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
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
            return view('admin.users-pro.user-details', compact('user'));
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
            return view('admin.users-pro.edit',compact('user'));
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
            'company_name' => 'required|max:255',
            'reg_number' => 'required|max:255',
            'company_address' => 'required|max:255',
            'company_zip_code' => 'required|max:255',
            'company_city' => 'required|max:255',
            'country_code' => 'required|max:25',
            'mobile' => 'required|digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $user = User::findOrFail($id);
            if ($request->has('mobile')) {
                $u = User::where('mobile', $request->mobile)->where('id', '<>', $id)->get();
                if (count($u) > 0) {
                    return back()->with('flash_error', trans('This number are already exist'));
                }

                // QrCode generator
                $file=QrCode::format('png')->size(500)->margin(10)->generate('{
                    "country_code":'.'"'.$request->country_code.'"'.',
                    "phone_number":'.'"'.$request->mobile.'"'.'
                    }');
                // $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
                $user->qrcode_url = Helper::upload_qrCode($request->mobile, $file);
            }
            $user->company_name = $request->company_name;
            $user->reg_number = $request->reg_number;
            $user->company_address = $request->company_address;
            if ($request->latitude && $request->longitude) {
                $user->latitude = $request->latitude;
                $user->longitude = $request->longitude;
            }
            if ($request->wallet_limit) {
                $user->wallet_limit = $request->wallet_limit;
            }
            if($request->hasFile('picture')) {
                $user['picture'] = $request->picture->store('user/profile');
            }

            $user->country_code = $request->country_code;
            $user->company_zip_code = $request->company_zip_code;
            $user->company_city = $request->company_city;
            $user->mobile = $request->mobile;
            $user->allow_negative = $request->allow_negative ? $request->allow_negative : 0;
            $user->save();

            return redirect()->route('admin.user-pro.index')->with('flash_success', trans('admin.user_msgs.user_update'));
        }

        catch (ModelNotFoundException $e) {
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
        }
        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.user_msgs.user_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Provider  $provider
     * @return Response
     */
    public function request($id){

        try{

            $requests = UserRequests::where('user_requests.user_id',$id)
                    ->RequestHistory()
                    ->paginate($this->perpage);

            $pagination=(new Helper)->formatPagination($requests);

            return view('admin.request.index', compact('requests','pagination'));
        }

        catch (Exception $e) {
             return back()->with('flash_error', trans('admin.something_wrong'));
        }

    }

}

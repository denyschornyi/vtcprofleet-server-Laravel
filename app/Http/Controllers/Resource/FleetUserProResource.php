<?php

namespace App\Http\Controllers\Resource;

use App\Fleet;
use App\Provider;
use App\User;
use App\UserRequests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Storage;
use Setting;
use QrCode;

class FleetUserProResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store', 'update','destroy']]);

     /*   $this->middleware('permission:user-list', ['only' => ['index']]);
        $this->middleware('permission:user-create', ['only' => ['create','store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);*/

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
            $users = User::where(['user_type'=>'FLEET_COMPANY','fleet_id'=>Auth::user('fleet')->id])->orderBy('id' , 'asc')->get();//->where('fleet_company_id','<>',0)
            return response()->json(array('success' => true, 'data'=>$users));
        }
        else{

            $users = User::where(['user_type'=>'FLEET_COMPANY','fleet_id'=>Auth::user('fleet')->id])->orderBy('id' , 'desc')->paginate($this->perpage);
            $pagination=(new Helper)->formatPagination($users);
            return view('fleet.users-pro.index', compact('users','pagination'));
        }


    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('fleet.users-pro.create');
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
            $user['user_type'] = 'FLEET_COMPANY';

            // QrCode generator
            $file=QrCode::format('png')->size(500)->margin(10)->generate('{
                "country_code":'.'"'.$request->country_code.'"'.',
                "phone_number":'.'"'.$request->mobile.'"'.'
            }');
            // $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
            $user['qrcode_url'] = Helper::upload_qrCode($request->mobile,$file);

            if (empty($user['wallet_limit']))   $user['wallet_limit'] = 0;
	        $user['wallet_limit'] = $request->input('wallet_limit');

	        $user['fleet_id'] = Auth::user('fleet')->id;

            $user = User::create($user);

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
            return view('fleet.users-pro.user-details', compact('user'));
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
            return view('fleet.users-pro.edit',compact('user'));
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
            $user->update();

            return redirect()->route('fleet.user-pro.index')->with('flash_success', trans('admin.user_msgs.user_update'));
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
            
            $passengerIDs = User::where('user_type','FLEET_PASSENGER')->where('fleet_company_id',$id)->pluck('id')->toArray();
           
            $passengerIDs[] = $id;
            // var_dump($passengerIDs);
            // exit;

//            $requests = UserRequests::whereIn('user_requests.user_id',$passengerIDs)
//                    ->RequestHistory()
//                    ->paginate($this->perpage);

	        $requests = DB::table( 'user_requests' )->join( 'users',
		        'user_requests.user_id',
		        '=',
		        'users.id' )
		        ->leftjoin( 'user_request_payments',
			        'user_requests.id',
			        '=',
			        'user_request_payments.request_id' )
		        ->join('providers',
			        'user_requests.provider_id',
			        'providers.id')
		        ->join( 'service_types',
			        'user_requests.service_type_id',
			        '=',
			        'service_types.id' )
		        ->whereIn( 'user_requests.user_id', $passengerIDs )
		        ->whereNull( 'user_requests.deleted_at' )
//		        ->where( 'user_requests.paid', 1 )
				->select( 'user_requests.id AS ids',
					'users.*',
					'user_requests.*',
					'user_request_payments.*',
					'providers.first_name as provider_first',
					'providers.first_name as provider_last',
					'service_types.calculator' )
		        ->orderBy( 'user_requests.created_at', 'desc' );
            
            // $requests = UserRequests::whereIn('user_id', $passengerIDs)->whereNull('deleted_at')->with('user', 'provider', 'userrequestpayment', 'service');

            $requests = $requests->get();
            
            // foreach($requests as $request){
            //     var_dump($request->created_at);
            // }
            // exit;
//            $pagination=(new Helper)->formatPagination($requests);

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

            // $fleet = Fleet::where( 'id', Auth::guard( 'fleet' )->id() )->get();
            $fleet = Fleet::where('id', Auth::user()->id)->first();
            // var_dump($fleet->name);
            // exit;
            return view('fleet.request.index', compact('requests','dates','fleet'));
        }

        catch (Exception $e) {
             return back()->with('flash_error', trans('admin.something_wrong'));
        }

    }

    public function user_request(Request $request, $id) {
        try {
            

			$userFleetRequests     =
				UserRequests::with( 'payment', 'user', 'provider' )
					->where( 'user_id', $id )
					->orderBy( 'created_at', 'desc' );
			
			if ( $request->from_date && $request->to_date ) {
				if ( $request->from_date == $request->to_date ) {
					$userFleetRequests->whereDate( 'user_requests.created_at',
						date( 'Y-m-d', strtotime( $request->from_date ) ) );
				} else {
					$userFleetRequests->whereBetween( 'user_requests.created_at',
						[
							Carbon::createFromFormat( 'Y-m-d',
								$request->from_date ),
							Carbon::createFromFormat( 'Y-m-d',
								$request->to_date ),
						] );
					
				}
			}

			
			$requests = $userFleetRequests->get();
			

			$dates['yesterday']       = Carbon::yesterday()->format( 'Y-m-d' );
			$dates['today']           = Carbon::today()->format( 'Y-m-d' );
			$dates['pre_week_start']  =
				date( "Y-m-d", strtotime( "last week monday" ) );
			$dates['pre_week_end']    =
				date( "Y-m-d", strtotime( "last week sunday" ) );
			$dates['cur_week_start']  =
				Carbon::today()->startOfWeek()->format( 'Y-m-d' );
			$dates['cur_week_end']    =
				Carbon::today()->endOfWeek()->format( 'Y-m-d' );
			$dates['pre_month_start'] =
				Carbon::parse( 'first day of last month' )->format( 'Y-m-d' );
			$dates['pre_month_end']   =
				Carbon::parse( 'last day of last month' )->format( 'Y-m-d' );
			$dates['cur_month_start'] =
				Carbon::parse( 'first day of this month' )->format( 'Y-m-d' );
			$dates['cur_month_end']   =
				Carbon::parse( 'last day of this month' )->format( 'Y-m-d' );
			$dates['pre_year_start']  =
				date( "Y-m-d", strtotime( "last year January 1st" ) );
			$dates['pre_year_end']    =
				date( "Y-m-d", strtotime( "last year December 31st" ) );
			$dates['cur_year_start']  =
				Carbon::parse( 'first day of January' )->format( 'Y-m-d' );
			$dates['cur_year_end']    =
				Carbon::parse( 'last day of December' )->format( 'Y-m-d' );
			$dates['nextWeek']        =
				Carbon::today()->addWeek()->format( 'Y-m-d' );

			$fleet = Fleet::where( 'id', Auth::guard( 'fleet' )->id() )->first();
			return view( 'fleet.request.index',
				compact( 'requests', 'dates', 'fleet' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
    }

}

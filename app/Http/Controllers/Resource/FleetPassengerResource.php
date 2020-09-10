<?php

namespace App\Http\Controllers\Resource;

use App\Admin;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Provider;
use App\User;
use App\UserRequests;
use Carbon\Carbon;
use function currency;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use QrCode;
use Setting;
use Storage;

class FleetPassengerResource extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	protected $UserAPI;

	public function __construct() {

		$this->middleware( 'auth', [ 'except' => [ 'save_subscription' ] ] );
		$this->middleware( 'demo',
			[
				'only' => [
					'update_password',
				],
			] );
//		$this->middleware('permission:ride-delete', ['only' => ['destroy']]);

		$this->perpage = config( 'constants.per_page', '10' );
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index( Request $request ) {
		if ( ! empty( $request->download ) && $request->download == 'all' ) {
			$users = User::where( 'fleet_company_id', '=', Auth::user()->id )->where('user_type','=','FLEET_PASSENGER')
			             ->orderBy( 'id', 'asc' )->get();

			return response()->json( array(
				'success' => true,
				'data'    => $users,
			) );
		}
		// else {
		//     $users = User::where('user_type', '<>', 'COMPANY')->orderBy('created_at', 'desc')->paginate($this->perpage);
		//     $pagination = (new Helper)->formatPagination($users);
		//     return view('admin.users.index', compact('users', 'pagination'));
		// }

		if ( $request->ajax() ) {
			$start        = $request->start;
			$length       = $request->length;
			$search_value = $request->search['value'];
			$orders       = [
				'id',
				'first_name',
				'last_name',
				'email',
				'mobile',
				'rating',
				'wallet_balance',
				'id',
			];
			$order_name   = $orders[ intval( $request->order[0]['column'] ) ];
			// $order_name = $request->columns[intval($request->order[0]['column'])]['name'];
			$dir  = $request->order[0]['dir'];
			$draw = $request->draw;

			$users = User::where( 'fleet_company_id', '=', Auth::user()->id )->where('user_type','=','FLEET_PASSENGER')
			             ->where( function ( $query ) use ( $search_value ) {
				             $query->orWhere( 'id',
					             'like',
					             '%' . $search_value . '%' )
				                   ->orWhere( 'first_name',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'last_name',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'email',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'mobile',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'wallet_balance',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'rating',
					                   'like',
					                   '%' . $search_value . '%' );
			             } )
			             ->orderBy( empty( $order_name ) ? 'id' : $order_name,
				             $dir )
			             ->offset( $start )
			             ->limit( $length )
			             ->get();
			foreach ( $users as $key => $user ) {
				$user->wallet_balance = currency( $user->wallet_balance );
				$users[ $key ]        = $user;
			}
			$count = User::where( 'fleet_company_id', '=', Auth::user()->id )->where('user_type','=','FLEET_PASSENGER')
			             ->where( function ( $query ) use ( $search_value ) {
				             $query->orWhere( 'id',
					             'like',
					             '%' . $search_value . '%' )
				                   ->orWhere( 'first_name',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'last_name',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'email',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'mobile',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'wallet_balance',
					                   'like',
					                   '%' . $search_value . '%' )
				                   ->orWhere( 'rating',
					                   'like',
					                   '%' . $search_value . '%' );
			             } )
			             ->orderBy( empty( $order_name ) ? 'id' : $order_name,
				             $dir )
			             ->count();

			$total = User::where( 'fleet_company_id', '=', Auth::user()->id )->where('user_type','=','FLEET_PASSENGER')
			             ->orderBy( empty( $order_name ) ? 'id' : $order_name,
				             $dir )
			             ->count();

			$result                    = array();
			$result['recordsTotal']    = $total;
			$result['recordsFiltered'] = $count;
			$result['data']            = $users;
			$result['draw']            = $draw;
			$result['aaaaa']           = $request->all();

			return response()->json( $result );
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

		return view( 'user.fleet.index' );
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return view( 'user.fleet.create' );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function store( Request $request ) {


		$this->validate( $request,
			[
				'first_name'   => 'required|max:255',
				'last_name'    => 'required|max:255',
				'email'        => 'required|unique:users,email|email|max:255',
				'country_code' => 'required|max:25',
				'mobile'       => 'digits_between:6,13|unique:users,mobile',
				'picture'      => 'mimes:jpeg,jpg,bmp,png|max:5242880',
				'password'     => 'required|min:6|confirmed',
			] );

		try {

			$user = $request->all();

			$user['payment_mode'] = 'CASH';
			$user['password']     = bcrypt( $request->password );
			if ( $request->hasFile( 'picture' ) ) {
				$user['picture'] = $request->file('picture')->store( 'user/profile' );
			}
			// QrCode generator

			$file = QrCode::format( 'png' )->size( 500 )->margin( 10 )
			              ->generate( '{
                "country_code":' . '"' . $request->country_code . '"' . ',
                "phone_number":' . '"' . $request->mobile . '"' . '
                }' );
			// $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
			$user['qrcode_url'] = Helper::upload_qrCode( $request->mobile,
				$file );

			$user['company_name'] = User::where('id',Auth::user()->id)->value('company_name');
			$user['company_address'] = User::where('id',Auth::user()->id)->value('company_address');
			$user['company_city'] = User::where('id',Auth::user()->id)->value('company_city');
			$user['company_zip_code'] = User::where('id',Auth::user()->id)->value('company_zip_code');
			$user['reg_number'] = User::where('id',Auth::user()->id)->value('reg_number');
			$user['user_type'] = 'FLEET_PASSENGER';

			//wallet negative and wallet_limit
			$user['fleet_id'] = Auth::user()->fleet_id;
			$user['fleet_company_id'] = Auth::user()->id;

			//2019.9.14 fleet passenger don't have negative wallet
			if(Auth::user()->allow_negative === 1)
			{
				$user['allow_negative'] = 1;
				$user['wallet_limit'] = Auth::user()->wallet_limit;
			}

			$user = User::create( $user );
			Helper::welcomeEmailToNewUser( 'user', User::find( $user->id ) );

			return back()->with( 'flash_success',
				trans( 'admin.user_msgs.user_saved' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.user_msgs.user_not_found' ) );
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param User $user
	 *
	 * @return Response
	 */
	public function show( $id ) {
		try {
			$user = User::findOrFail( $id );

			return view( 'admin.users.user-details', compact( 'user' ) );
		} catch ( ModelNotFoundException $e ) {
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
	public function edit( $id ) {
		try {
			$user = User::findOrFail( $id );

			return view( 'user.fleet.edit', compact( 'user' ) );
		} catch ( ModelNotFoundException $e ) {
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
	public function update( Request $request, $id ) {
		$this->validate( $request,
			[
				'first_name'   => 'required|max:255',
				'last_name'    => 'required|max:255',
				'country_code' => 'required|max:25',
				'mobile'       => 'digits_between:6,13',
				'picture'      => 'mimes:jpeg,jpg,bmp,png|max:5242880',
			] );

		try {

			$user = User::findOrFail( $id );

			if ( $request->hasFile( 'picture' ) ) {
				Storage::delete( $user->picture );
				$user->picture = $request->picture->store( 'user/profile' );
			}
			// QrCode generator
			$file = QrCode::format( 'png' )->size( 500 )->margin( 10 )
			              ->generate( '{
                "country_code":' . '"' . $request->country_code . '"' . ',
                "phone_number":' . '"' . $request->mobile . '"' . '
                }' );
			// $file=QrCode::format('png')->size(200)->margin(20)->phoneNumber($request->country_code.$request->mobile);
			$user->qrcode_url   = Helper::upload_qrCode( $request->mobile,
				$file );
			$user->first_name   = $request->first_name;
			$user->last_name    = $request->last_name;
			$user->country_code = $request->country_code;
			$user->mobile       = $request->mobile;
			$user->save();

			return redirect()->route( 'user-passenger.index' )
			                 ->with( 'flash_success',
				                 trans( 'admin.user_msgs.user_update' ) );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.user_msgs.user_not_found' ) );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param User $user
	 *
	 * @return Response
	 */
	public function destroy( $id ) {

		try {

			User::find( $id )->delete();

			return back()->with( 'message',
				trans( 'admin.user_msgs.user_delete' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.user_msgs.user_not_found' ) );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Provider $provider
	 *
	 * @return Response
	 */
	public function request( $id ) {

		try {

//			$requests = UserRequests::where( 'user_requests.user_id', 1 )
			$requests = UserRequests::where( 'user_requests.user_id',$id )
			                        ->RequestHistory()
			                        ->paginate( $this->perpage );

			$pagination = ( new Helper )->formatPagination( $requests );
			$trips      = $requests;
			$admin      = Admin::where( 'id', 1 )->first();

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

			return view( 'user.request.index',
				compact( 'requests', 'pagination', 'trips', 'admin','dates' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.something_wrong' ) );
		}
	}

	public function wallet_transfer(Request $request)
	{
		$wallet_amount = $request->input('wallet_amount');
		$id = $request->input('id');
		$user_pro_wallet_balance = Auth::user()->wallet_balance;
		$user_pro_wallet_limit = Auth::user()->wallet_limit;

		if(Auth::user()->allow_negative === '0')
		{
			if($wallet_amount > $user_pro_wallet_balance){
				return response()->json(['success' => false,'amount'=>$user_pro_wallet_balance]);
			}else{
				//increase passenger wallet amount
				$passenger_balance = User::where(id,$id)->value('wallet_balance');
				$user = User::findorFail($id);
				$user->wallet_balance = $wallet_amount+$passenger_balance;
				$user->update();
				//decrease user pro wallet amount
				$user_pro_balance = User::where(id,Auth::user()->id)->value('wallet_balance');
				$user1 = User::findorFail(Auth::user()->id);
				$user1->wallet_balance = $user_pro_balance-$wallet_amount;
				$user1->update();
				return response()->json(['success' => true]);
			}
		}else if(Auth::user()->allow_negative === '1')
		{
			if($user_pro_wallet_balance+$user_pro_wallet_limit>$wallet_amount){
				//increase passenger wallet amount
				$passenger_balance = User::where(id,$id)->value('wallet_balance');
				$user = User::findorFail($id);
				$user->wallet_balance = $wallet_amount+$passenger_balance;
				$user->update();
				//decrease user pro wallet amount
				$user_pro_balance = User::where(id,Auth::user()->id)->value('wallet_balance');
				$user1 = User::findorFail(Auth::user()->id);
				$user1->wallet_balance = $user_pro_balance-$wallet_amount;
				$user1->update();
				return response()->json(['success' => true]);
			}else{
				return response()->json(['success' => false,'limit'=>1,'amount'=>$user_pro_wallet_balance]);
			}
		}
	}
}

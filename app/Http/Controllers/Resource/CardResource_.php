<?php

namespace App\Http\Controllers\Resource;

use App\Card;
use App\FleetPaymentSettings;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Setting;
use Stripe\Customer;
use Stripe\Stripe;

class CardResource extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
		try {

			$cards = Card::where( 'user_id', Auth::user()->id )
				->orderBy( 'created_at', 'desc' )->get();
//			return $this->customer_id();
			return $cards;
		} catch ( Exception $e ) {
			return response()->json( [ 'error' => $e->getMessage() ], 500 );
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create() {
		//
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
				'stripe_token' => 'required',
				'user_id' => 'required'
			] );

		$user_id = $request->user_id;
		try {
			//get customer ID
			$customer_id = $this->customer_id($user_id);
			//set stripe key
			$this->set_stripe($user_id);

			$customer = Customer::retrieve( $customer_id );
			$card     = $customer->sources->create( [ "source" => $request->stripe_token ] );

			$exist = Card::where( 'user_id', $user_id )
				->where( 'last_four', $card['last4'] )
				->where( 'brand', $card['brand'] )
				->count();

			if ( $exist == 0 ) {

				$create_card            = new Card;
				$create_card->user_id   = Auth::user()->id;
				$create_card->card_id   = $card['id'];
				$create_card->last_four = $card['last4'];
				$create_card->brand     = $card['brand'];
				$create_card->save();
			} else {
				if ( $request->ajax() ) {
					return response()->json( [ 'message' => trans( 'api.card_already' ) ] );
				} else {
					return back()->with( 'flash_error',
						trans( 'api.card_already' ) );
				}
			}

			if ( $request->ajax() ) {
				return response()->json( [ 'message' => trans( 'api.card_added' ) ] );
			} else {
				return back()->with( 'flash_success',trans( 'api.card_added' ) );
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => $e->getMessage() ], 500 );
			} else {
				return back()->with( 'flash_error', $e->getMessage() );
			}
		}
	}

	/**
	 * Get a stripe customer id.
	 *
	 * @return Response
	 */
	public function customer_id($user_id = 1) {
		if ( Auth::user()->stripe_cust_id != null ) {

			return Auth::user()->stripe_cust_id;
		} else {

			try {

				$stripe = $this->set_stripe($user_id);

				$customer = Customer::create( [
					'email' => Auth::user()->email,
				] );

				User::where( 'id', $user_id )
					->update( [ 'stripe_cust_id' => $customer['id'] ] );

				return $customer['id'];
			} catch ( Exception $e ) {
				return $e;
			}
		}
	}

	/**
	 * setting stripe.
	 *
	 * @return Response
	 */
	public function set_stripe($user_id = 1)
	{
		//modify by fleet logic
		$userData = User::where('id',$user_id)->first();
		if($userData['user_type']=== 'FLEET_COMPANY' || $userData['user_type']=== 'FLEET_PASSENGER' || $userData['user_type']=== 'FLEET_NORMAL' )
		{
			$paymentData = FleetPaymentSettings::where('fleet_id',$userData['fleet_id'])->first();
			if($paymentData['stripe_payment_status']==='yes')
			{
				return Stripe::setApiKey( $paymentData['stripe_secret_key'] !== '' ? $paymentData['stripe_secret_key']  : '' ); //get fleet's payment stripe id
			}
		}elseif($userData['user_type'] === 'COMPANY' ||$userData['user_type'] === 'NORMAL'){
			return Stripe::setApiKey( config( 'constants.stripe_secret_key', '' ) ); //admin will use it
		}

//		return Stripe::setApiKey( config( 'constants.stripe_secret_key', '' ) );
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	public function show( $id ) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	public function edit( $id ) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param int     $id
	 *
	 * @return Response
	 */
	public function update( Request $request, $id ) {
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 *
	 * @return Response
	 */
	public function destroy( Request $request ) {

		$this->validate( $request,
			[
				'card_id' => 'required|exists:cards,card_id,user_id,'
				             . Auth::user()->id,
			] );

		try {


			$this->set_stripe(Auth::user()->id);

			$customer = Customer::retrieve( Auth::user()->stripe_cust_id );
			$customer->sources->retrieve( $request->card_id )->delete();

			Card::where( 'card_id', $request->card_id )->delete();

			if ( $request->ajax() ) {
				return response()->json( [ 'message' => trans( 'api.card_deleted' ) ] );
			} else {
				return back()->with( 'flash_success',
					trans( 'api.card_deleted' ) );
			}
		} catch ( Exception $e ) {
			if ( $request->ajax() ) {
				return response()->json( [ 'error' => $e->getMessage() ], 500 );
			} else {
				return back()->with( 'flash_error', $e->getMessage() );
			}
		}
	}
}

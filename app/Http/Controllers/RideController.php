<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\User;
use Auth;
use Braintree_ClientToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RideController extends Controller {
	protected $UserAPI;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct( UserApiController $UserAPI ) {
		$this->middleware( 'auth' );
		$this->UserAPI = $UserAPI;
	}


	/**
	 * Ride Confirmation.
	 *
	 * @return Response
	 */
	public function confirm_ride( Request $request ) {

		$fare      = $this->UserAPI->estimated_fare( $request )->getData();
		$service   =
			( new Resource\ServiceResource )->show( $request->service_type );
		$cards     = ( new Resource\CardResource )->index();
		$promolist = $this->UserAPI->list_promocode( $request );

		if ( $request->has( 'current_longitude' )
		     && $request->has( 'current_latitude' )
		) {
			User::where( 'id', Auth::user()->id )->update( [
				'latitude'  => $request->current_latitude,
				'longitude' => $request->current_longitude,
			] );
		}

		if ( config( 'constants.braintree' ) == 1 ) {
			$this->UserAPI->set_Braintree();
			$clientToken = Braintree_ClientToken::generate();
		} else {
			$clientToken = '';
		}

		$origin      = $request->s_latitude . "," . $request->s_longitude;
		$destination = $request->d_latitude . "," . $request->d_longitude;

		$markers = array();

		$markers[] = "markers=icon:" . asset( 'asset/img/marker-start.png' )
		             . urlencode( "|" ) . $origin;
		$markers[] = "markers=icon:" . asset( 'asset/img/marker-start.png' )
		             . urlencode( "|" ) . $destination;

		$url =
			"https://maps.googleapis.com/maps/api/directions/json?origin=$origin&destination=$destination&mode=driving&key="
			. config( 'constants.map_key' );

		$result = Helper::curl( $url );

		$googleDirection = json_decode( $result, true );

		$polyline =
			urlencode( $googleDirection['routes'][0]['overview_polyline']['points'] );
		$markers  = implode( $markers, '&' );

		$staticmap     =
			"https://maps.googleapis.com/maps/api/staticmap?size=600x450&maptype=roadmap&path=enc:$polyline&$markers&key="
			. config( 'constants.map_key' );
		$total         = $fare->estimated_fare - 0;
		$usable_wallet = Auth::user()->wallet_balance > 0
		                 || ( Auth::user()->user_type == 'COMPANY'
		                      && Auth::user()->allow_negative == 1
		                      && abs( Auth::user()->wallet_balance - $total )
		                         <= Auth::user()->wallet_limit );

		return view( 'user.ride.confirm_ride',
			compact( 'request',
				'fare',
				'service',
				'cards',
				'promolist',
				'clientToken',
				'staticmap',
				'usable_wallet',
				'total' ) );
	}

	/**
	 * Create Ride.
	 *
	 * @return Response
	 */
	public function create_ride( Request $request ) {
		return $this->UserAPI->send_request_user( $request );
	}

	/**
	 * Get Request Status Ride.
	 *
	 * @return Response
	 */
	public function status() {
		return $this->UserAPI->request_status_check();
	}

	/**
	 * Cancel Ride.
	 *
	 * @return Response
	 */
	public function cancel_ride( Request $request ) {
		return $this->UserAPI->cancel_request( $request );
	}

	/**
	 * Rate Ride.
	 *
	 * @return Response
	 */
	public function rate( Request $request ) {
		return $this->UserAPI->rate_provider( $request );
	}
}

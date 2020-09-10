<?php

namespace App\Http\Controllers\Resource;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Notifications;
use App\Reason;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SendPushNotification;
use App\User;
use App\Provider;

class FleetNotificationResource extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'demo',
			[ 'only' => [ 'store', 'update', 'destroy' ] ] );
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
		$notification =
			Notifications::where( 'fleet_id', Auth::guard( 'fleet' )->id() )
				->orderBy( 'created_at', 'desc' )->get();

		return view( 'fleet.notification.index', compact( 'notification' ) );
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create() {
		return view( 'fleet.notification.create' );
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
				'notify_type' => 'required',
				'image'       => 'required|mimes:jpeg,jpg,png|max:5242880',
			] );

		try {

			$Notifications = new Notifications;
			$Notifications->notify_type = $request->notify_type;

			if ( $request->hasFile( 'image' ) ) {
				$Notifications->image =
					Helper::upload_picture( $request->image );
			}

			$Notifications->description = $request->description;
			$Notifications->expiry_date =
				date( 'Y-m-d H:i:s', strtotime( $request->expiry_date ) );
			$Notifications->status      = $request->status;
			$Notifications->fleet_id    = Auth::guard( 'fleet' )->id();
			$Notifications->save();

			// send push notifications to related users and providers with admin.
			try {
				if ($request->notify_type === 'user') {
					$users = User::where('fleet_id', $Notifications->fleet_id)->get();
					$apns = new SendPushNotification;
					foreach ($users as $user) {
						$apns->sendPushToUser($user->id, 'New Notification');
					}
					
				} elseif ($request->notify_type === 'provider') {
					$providers = User::where('fleet', $Notifications->fleet_id)->get();
					$apns = new SendPushNotification;
					foreach ($providers as $provider) {
						$apns->sendPushToProvider($provider->id, 'New Notification');
					}
				} else {
					$apns = new SendPushNotification;
					$users = User::where('fleet_id', $Notifications->fleet_id)->get();
					foreach ($users as $user) {
						$apns->sendPushToUser($user->id, 'New Notification');
					}
					$providers = User::where('fleet', $Notifications->fleet_id)->get();
					foreach ($providers as $provider) {
						$apns->sendPushToProvider($provider->id, 'New Notification');
					}
				}
			} catch (\Throwable $th) {
				
			}
			
			return back()->with( 'flash_success',
				trans( 'admin.notification_msgs.saved' ) );

		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.notification_msgs.not_found' ) );
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param Reason $id
	 *
	 * @return Response
	 */
	public function show( $id ) {
		try {
			return Notifications::findOrFail( $id );
		} catch ( ModelNotFoundException $e ) {
			return $e;
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param Reason $id
	 *
	 * @return Response
	 */
	public function edit( $id ) {
		try {
			$notification = Notifications::findOrFail( $id );

			return view( 'fleet.notification.edit', compact( 'notification' ) );
		} catch ( ModelNotFoundException $e ) {
			return $e;
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param Reason  $id
	 *
	 * @return Response
	 */
	public function update( Request $request, $id ) {

		$this->validate( $request,
			[
				'notify_type' => 'required',
				'image'       => 'mimes:jpeg,jpg,png|max:5242880',
			] );

		try {

			$Notifications = Notifications::findOrFail( $id );

			$Notifications->notify_type = $request->notify_type;

			if ( $request->hasFile( 'image' ) ) {
				if ( $Notifications->image ) {
					Helper::delete_picture( $Notifications->image );
				}
				$Notifications->image =
					Helper::upload_picture( $request->image );
			}

			$Notifications->description = $request->description;
			$Notifications->expiry_date =
				date( 'Y-m-d H:i:s', strtotime( $request->expiry_date ) );
			$Notifications->status      = $request->status;
			$Notifications->save();

			return redirect()->route( 'fleet.notification.index' )
				->with( 'flash_success',
					trans( 'admin.notification_msgs.update' ) );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.notification_msgs.not_found' ) );
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Reason $id
	 *
	 * @return Response
	 */
	public function destroy( $id ) {
		try {
			Notifications::find( $id )->delete();

			return back()->with( 'flash_success',
				trans( 'admin.notification_msgs.delete' ) );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.notification_msgs.not_found' ) );
		}
	}

	/**
	 * get notifications for respcted types
	 */

}

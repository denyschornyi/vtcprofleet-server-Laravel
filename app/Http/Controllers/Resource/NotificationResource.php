<?php

namespace App\Http\Controllers\Resource;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Notifications;
use App\Reason;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SendPushNotification;
use App\User;
use App\Provider;

class NotificationResource extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'demo',
			[ 'only' => [ 'store', 'update', 'destroy' ] ] );
		$this->middleware( 'permission:notification-list',
			[ 'only' => [ 'index' ] ] );
		$this->middleware( 'permission:notification-create',
			[ 'only' => [ 'create', 'store' ] ] );
		$this->middleware( 'permission:notification-edit',
			[ 'only' => [ 'edit', 'update' ] ] );
		$this->middleware( 'permission:notification-delete',
			[ 'only' => [ 'destroy' ] ] );
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
		$notification = Notifications::where('fleet_id',0)->orderBy( 'created_at', 'desc' ) ->get();

		return view( 'admin.notification.index', compact( 'notification' ) );
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create() {
		return view( 'admin.notification.create' );
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
			$Notifications->fleet_id    = 0;
			$Notifications->save();

			// send push notifications to related users and providers with admin.
			try {
				if ($request->notify_type === 'user') {
					$users = User::where('fleet_id', 0)->get();
					$apns = new SendPushNotification;
					foreach ($users as $user) {
						$apns->sendPushToUser($user->id, 'New Notification');
					}
					
				} elseif ($request->notify_type === 'provider') {
					$providers = User::where('fleet', 0)->get();
					$apns = new SendPushNotification;
					foreach ($providers as $provider) {
						$apns->sendPushToProvider($provider->id, 'New Notification');
					}
				} else {
					$apns = new SendPushNotification;
					$users = User::where('fleet_id', 0)->get();
					foreach ($users as $user) {
						$apns->sendPushToUser($user->id, 'New Notification');
					}
					$providers = User::where('fleet', 0)->get();
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

			return view( 'admin.notification.edit', compact( 'notification' ) );
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

			return redirect()->route( 'admin.notification.index' )
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
	public function getnotify($type)
	{

		if($type=='user'){
			$except_type='provider';
			$fleet_id = Auth::user()->fleet_id; 
		}elseif($type='provider'){
			$except_type='user';
			$fleet_id = Auth::user()->fleet;
		}
//		return response()->json(['type'=>$type, 'fleet_id'=>$fleet_id]);
		try {
			$notification = Notifications::where('notify_type', '!=', $except_type)
				->where('status', 'active')
				->where('fleet_id', $fleet_id)
				->orderBy('created_at' , 'desc')->get();
			return response()->json($notification);
		}
		catch (Exception $e) {
			return response()->json(['error' => trans('api.something_went_wrong')], 500);
		}
	}
}

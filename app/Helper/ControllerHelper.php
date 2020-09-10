<?php

namespace App\Helpers;

use App\FleetServiceType;
use App\ServiceType;
use function appDateTime;
use Carbon\Carbon;
use function currency;
use File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Setting;
use Illuminate\Support\Facades\Mail;
use App\WalletRequests;
use App\WalletPassbook;
use stdClass;
use Throwable;
use Twilio\Rest\Client;
use App\UserRequests;
use App\Services\ServiceTypes;
use App\Provider;
use App\Fleet;
use Sly\NotificationPusher\PushManager,
	Sly\NotificationPusher\Adapter\Gcm as GcmAdapter,
	Sly\NotificationPusher\Collection\DeviceCollection,
	Sly\NotificationPusher\Model\Device,
	Sly\NotificationPusher\Model\Message,
	Sly\NotificationPusher\Model\Push;

class Helper
{

	public static function upload_picture($picture)
	{
		$file_name = time();
		$file_name .= rand();
		$file_name = sha1($file_name);
		if ($picture) {
			$ext = $picture->getClientOriginalExtension();
			$picture->move(public_path() . "/uploads", $file_name . "." . $ext);
			$local_url = $file_name . "." . $ext;

			$s3_url = url('/') . '/uploads/' . $local_url;

			return $s3_url;
		}
		return "";
	}

	public static function upload_picture2($picture)
	{
		$file_name = time();
		$file_name .= rand();
		$file_name = sha1($file_name);
		if ($picture) {
			$ext = $picture->getClientOriginalExtension();
			$picture->move(public_path() . "/uploads", $file_name . "." . $ext);
			$local_url = $file_name . "." . $ext;

			$s3_url = '/uploads/' . $local_url;

			return $s3_url;
		}
		return "";
	}

	public static function delete_picture($picture)
	{
		File::delete(public_path() . "/uploads/" . basename($picture));
		return true;
	}
	public static function routeCheck($routeName)
	{

		return true;
	}

	public static function generate_booking_id()
	{
		return config('constants.booking_prefix') . mt_rand(100000, 999999);
	}

	public static function site_sendmail($user)
	{

		$site_details = Setting::all();

		Mail::send('emails.invoice', ['Email' => $user], function ($mail) use ($user, $site_details) {

			//$mail->to('tamilvanan@blockchainappfactory.com')->subject('Invoice');

			$mail->to($user->user->email, $user->user->first_name . ' ' . $user->user->last_name)->subject('Invoice');
		});

		/*if( count(Mail::failures()) > 0 ) {

		   echo "There was one or more failures. They were: <br />";

		   foreach(Mail::failures() as $email_address) {
			   echo " - $email_address <br />";
			}

		} else {
			echo "No errors, all sent successfully!";
		}*/

		return true;
	}

	public static function site_registermail($user)
	{

		$site_details = Setting::all();

		Mail::send('emails.welcome', ['user' => $user], function ($mail) use ($user) {
			// $mail->from('harapriya@appoets.com', 'Your Application');

			//$mail->to('tamilvanan@blockchainappfactory.com')->subject('Invoice');

			$mail->to($user->email, $user->first_name . ' ' . $user->last_name)->subject('Welcome');
		});

		if (count(Mail::failures()) > 0) {

			echo "There was one or more failures. They were: <br />";

			foreach (Mail::failures() as $email_address) {
				echo " - $email_address <br />";
			}
		} else {
			echo "No errors, all sent successfully!";
		}

		return true;
	}

	public function formatPagination($pageobj)
	{

		$results = new stdClass();

		$results->links = $pageobj->links();
		$results->count = $pageobj->count();
		$results->currentPage = $pageobj->currentPage();
		$results->firstItem = $pageobj->firstItem();
		$results->hasMorePages = $pageobj->hasMorePages();
		$results->lastItem = $pageobj->lastItem();
		$results->lastPage = $pageobj->lastPage();
		$results->nextPageUrl = $pageobj->nextPageUrl();
		$results->perPage = $pageobj->perPage();
		$results->previousPageUrl = $pageobj->previousPageUrl();
		$results->total = $pageobj->total();
		//$results->url=$pageobj->url();

		return $results;
	}

	public static function generate_request_id($type)
	{

		if ($type == 'provider') {
			$tr_str = 'PSET';
		} else {
			$tr_str = 'FSET';
		}

		$typecount = WalletRequests::where('request_from', $type)->count();

		if (!empty($typecount))
			$next_id = $typecount + 1;
		else
			$next_id = 1;

		$alias_id = $tr_str . str_pad($next_id, 6, 0, STR_PAD_LEFT);

		return $alias_id;
	}

	public static function generate_alias_id(){
		$count = WalletPassbook::count();
		$next_id = $count + 1;
		$alias_id = 'USET' . str_pad($next_id, 6, 0, STR_PAD_LEFT);
		return $alias_id;
	}

	public static function curl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return = curl_exec($ch);
		curl_close($ch);
		return $return;
	}


	public static function getAddress($latitude, $longitude)
	{

		if (!empty($latitude) && !empty($longitude)) {
			//Send request and receive json data by address
			$geocodeFromLatLong = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($latitude) . ',' . trim($longitude) . '&sensor=false&key=' . config('constants.map_key'));
			$output = json_decode($geocodeFromLatLong);
			$status = $output->status;
			//Get address from json data
			$address = ($status == "OK") ? $output->results[0]->formatted_address : '';
			//Return address of the given latitude and longitude
			if (!empty($address)) {
				return $address;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function upload_qrCode($phone, $file)
	{
		$file_name = time();
		$file_name .= rand();
		if ($file) {
			$fileName       = $file_name . '_' . $phone . ".png";
			file_put_contents(public_path() . '/uploads/' . $fileName, $file);
			$qrcode_url = 'uploads/' . $fileName;
			return $qrcode_url;
		}
		return "";
	}

	public static function emailToUserWhenScheduled($approved_bookingID)
	{
		// Data#1: Provider Full user name
		// Data#2: It's Booking ID
		// Data#3: Booking date
		// Data#4: Pick up address
		// Data#5: Drop off address
		// Data#6: Service type
		// Data#7: The note if user add some
		// Data#8: Passenger name
		// Data#9: Réccurent date like Mon, Tue, Wed....
		// Data#10: ETA (estimation time)
		// Data#11: Estimation price
		// Data#12: Company name like VTCPro
		// Data#13: Company website like www.memohi.fr
		// Data#14: Company contact like info@solutionweb.io
		$request = UserRequests::where('user_requests.id', $approved_bookingID)
			->leftJoin('user_request_recurrents', 'user_requests.user_req_recurrent_id', '=', 'user_request_recurrents.id')
			->select(['user_requests.*', 'user_request_recurrents.repeated as repeated'])
			->with('payment', 'service_type', 'user')
			->first();
		if (!empty($request->repeated)) {
			$dates = json_decode($request->repeated);
			$dateString = '';
			$dayOfWeeks = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
			$dayOfWeeksFr = array("dim", "lun", "mar", "mer", "jeu", "ven", "sam");
			foreach ($dates as $d) {
				$dateString .= "$dayOfWeeksFr[$d], ";
			}
			if (strlen($dateString) >= 2) {
				$dateString = substr($dateString, 0, strlen($dateString) - 2);
			}
			$request->repeatedString = $dateString;
		}

		// echo json_encode($request);
		// exit;

		if ($request) {
			$fare = new ServiceTypes();
			$params = array(
				's_latitude' => $request->s_latitude,
				's_longitude' => $request->s_longitude,
				'd_latitude' => $request->d_latitude,
				'd_longitude' => $request->d_longitude,
				'service_type' => $request->service_type_id
			);

			$faredata = $fare->calculateFare($params, 1);
			$faredata = $faredata['data'];
			// echo json_encode($faredata);
			// exit;

			try {
				$data = array(
					'data1' => $request->user->user_type == 'NORMAL' ? $request->user->first_name . ' ' . $request->user->last_name : $request->company_name,
					'data2' => $request->booking_id,
					'data3' => appDateTime($request->created_at),
					'data4' => $request->s_address,
					'data5' => $request->d_address,
					'data6' => $request->service_type->name,
					'data7' => $request->note ? $request->note : "",
					'data8' => $request->passenger_name ? $request->passenger_name : "",
					'data9' => $request->repeatedString,
					'data10' => $faredata['time'],
					'data11' => currency($faredata['estimated_fare']),
					'data12' => env('APP_NAME', 'VTCPro'),
					'data13' => env('APP_URL'),
					'data14' => 'info@solutionweb.io',
					// 'email' => 'liuwc0026@gmail.com',
					// 'email' => 'w.etisalat@gmail.com',
					'email' => $request->user->email,
				);
				// echo json_encode($data);
				// exit;

				Mail::send('emails.approved_to_provider', ['data' => $data], function ($mail) use ($data) {
					$mail->from($data['data14'], $data['data12']);
					$mail->to($data['email'], $data['data1'])->subject('Vocher');
				});
				// echo $user['email'];
				return true;
			} catch ( Throwable $th) {
				// echo json_encode('something went wrong');
				return false;
			}
		} else {
			// echo json_encode('empty booking');
			return false;
		}
	}

	public static function smsToUserWhenScheduled($approved_bookingID)
	{
		$enable = config('constants.sms_to_user');
		if ($enable == 0) return;
		// Data#1: Provider Full user name
		// Data#2: It's Booking ID
		// Data#3: Booking date
		// Data#4: Pick up address
		// Data#5: Drop off address
		// Data#6: Service type
		// Data#7: The note if user add some
		// Data#8: Passenger name
		// Data#9: Réccurent date like Mon, Tue, Wed....
		// Data#10: ETA (estimation time)
		// Data#11: Estimation price
		// Data#12: Company name like VTCPro
		// Data#13: Company website like www.memohi.fr
		// Data#14: Company contact like info@solutionweb.io
		$request = UserRequests::where('user_requests.id', $approved_bookingID)
			->leftJoin('user_request_recurrents', 'user_requests.user_req_recurrent_id', '=', 'user_request_recurrents.id')
			->select(['user_requests.*', 'user_request_recurrents.repeated as repeated'])
			->with('payment', 'service_type', 'user')
			->first();
		if (!empty($request->repeated)) {
			$dates = json_decode($request->repeated);
			$dateString = '';
			$dayOfWeeks = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
			$dayOfWeeksFr = array("dim", "lun", "mar", "mer", "jeu", "ven", "sam");
			foreach ($dates as $d) {
				$dateString .= "$dayOfWeeksFr[$d], ";
			}
			if (strlen($dateString) >= 2) {
				$dateString = substr($dateString, 0, strlen($dateString) - 2);
			}
			$request->repeatedString = $dateString;
		}

		// echo json_encode($request);
		// exit;

		if ($request) {
			$fare = new ServiceTypes();
			$params = array(
				's_latitude' => $request->s_latitude,
				's_longitude' => $request->s_longitude,
				'd_latitude' => $request->d_latitude,
				'd_longitude' => $request->d_longitude,
				'service_type' => $request->service_type_id
			);

			$faredata = $fare->calculateFare($params, 1);
			$faredata = $faredata['data'];
			// echo json_encode($faredata);
			// exit;

			try {
				$data = array(
					'data1' => $request->user->user_type == 'NORMAL' ? $request->user->first_name . ' ' . $request->user->last_name : $request->company_name,
					'data2' => $request->booking_id,
					'data3' => appDateTime($request->created_at),
					'data4' => $request->s_address,
					'data5' => $request->d_address,
					'data6' => $request->service_type->name,
					'data7' => $request->note ? $request->note : "",
					'data8' => $request->passenger_name ? $request->passenger_name : "",
					'data9' => $request->repeatedString,
					'data10' => $faredata['time'],
					'data11' => currency($faredata['estimated_fare']),
					'data12' => env('APP_NAME', 'VTCPro'),
					'data13' => env('APP_URL'),
					'data14' => 'info@solutionweb.io',
					'email' => $request->user->email,
				);
				// echo json_encode($data);
				// exit;

				$sid    = config('constants.twilio_sid', '');
				$token  = config('constants.twilio_token', '');
				$client = new Client($sid, $token);

				$number = '+33629957457';
				$number = $request->user->country_code . $request->user->mobile;
				$message = "Bonjour " . $data['data1'] . ", vous avez réservé avec succès un trajet de " . $data['data4'] . " à " . $data['data5'] . " le " . $data['data3'] . ".      Le n° de la course est le " . $data['data2'] . "      Suivez votre parcours sur notre application.   " . $data['data13'] . "";
				$client->messages->create(
					$number,
					[
						'from' => config('constants.twilio_from', ''),
						'body' => $message,
					]
				);

				return true;
			} catch ( Throwable $th) {
				// echo json_encode('something went wrong');
				return false;
			}
		} else {
			// echo json_encode('empty booking');
			return false;
		}
	}

	public static function emailToFleetWhenApproved($approved_bookingID)
	{
		// Data#1: Full user name
		// Data#2: It's Booking ID
		// Data#3: Booking date
		// Data#4: Pick up address
		// Data#5: Drop off address
		// Data#6: Service type
		// Data#7: The note if user add some
		// Data#8: Passenger name
		// Data#9: Réccurent date like Mon, Tue, Wed....
		// Data#10: ETA (estimation time)
		// Data#11: Estimation price
		// Data#12: Company name like VTCPro
		// Data#13: Company website like www.memohi.fr
		// Data#14: Company contact like info@solutionweb.io
		$request = UserRequests::where('user_requests.id', $approved_bookingID)
			->leftJoin('user_request_recurrents', 'user_requests.user_req_recurrent_id', '=', 'user_request_recurrents.id')
			->select(['user_requests.*', 'user_request_recurrents.repeated as repeated'])
			->with('payment', 'service_type', 'user')
			->first();
		if (!empty($request->repeated)) {
			$dates = json_decode($request->repeated);
			$dateString = '';
			$dayOfWeeks = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
			$dayOfWeeksFr = array("dim", "lun", "mar", "mer", "jeu", "ven", "sam");
			foreach ($dates as $d) {
				$dateString .= "$dayOfWeeksFr[$d], ";
			}
			if (strlen($dateString) >= 2) {
				$dateString = substr($dateString, 0, strlen($dateString) - 2);
			}
			$request->repeatedString = $dateString;
		}

		// echo json_encode($request);
		// exit;

		if ($request) {
			$fleet = Fleet::where('id', $request->fleet_id)->first();
			if (!$fleet) {
				return false;
			}
			$fare = new ServiceTypes();
			$params = array(
				's_latitude' => $request->s_latitude,
				's_longitude' => $request->s_longitude,
				'd_latitude' => $request->d_latitude,
				'd_longitude' => $request->d_longitude,
				'service_type' => $request->service_type_id
			);

			$faredata = $fare->calculateFare($params, 1);
			$faredata = $faredata['data'];
			// echo json_encode($faredata);
			// exit;

			try {
				$data = array(
					'data1' => $fleet->name,
					'data2' => $request->booking_id,
					'data3' => appDateTime($request->created_at),
					'data4' => $request->s_address,
					'data5' => $request->d_address,
					'data6' => $request->service_type->name,
					'data7' => $request->note ? $request->note : "",
					'data8' => $request->passenger_name ? $request->passenger_name : "",
					'data9' => $request->repeatedString,
					'data10' => $faredata['time'],
					'data11' => currency($faredata['estimated_fare']),
					'data12' => env('APP_NAME', 'VTCPro'),
					'data13' => env('APP_URL'),
					'data14' => 'info@solutionweb.io',
					// 'email' => 'liuwc0026@gmail.com',
					// 'email' => 'w.etisalat@gmail.com',
					'email' => $fleet->email,
				);
				// echo json_encode($data);
				// exit;

				Mail::send('emails.approved_to_fleet', ['data' => $data], function ($mail) use ($data) {
					$mail->from($data['data14'], $data['data12']);
					$mail->to($data['email'], $data['data1'])->subject('Vocher');
				});
				// echo $user['email'];
				return true;
			} catch ( Throwable $th) {
				// echo json_encode('something went wrong');
				return false;
			}
		} else {
			// echo json_encode('empty booking');
			return false;
		}
	}

	public static function smsToFleetWhenApproved($approved_bookingID)
	{
		$enable = config('constants.sms_to_fleet');
		if ($enable == 0) return;
		// Data#1: Fleet name
		// Data#2: It's Booking ID
		// Data#3: Scheduled date
		// Data#4: Pick up address
		// Data#5: Drop off address
		// Data#6: Service type
		// Data#7: The note if user add some
		// Data#8: Passenger name
		// Data#9: Réccurent date like Mon, Tue, Wed....
		// Data#10: ETA (estimation time)
		// Data#11: Estimation price
		// Data#12: Company name like VTCPro
		// Data#13: Company website like www.memohi.fr
		// Data#14: Company contact like info@solutionweb.io
		$request = UserRequests::where('user_requests.id', $approved_bookingID)
			->leftJoin('user_request_recurrents', 'user_requests.user_req_recurrent_id', '=', 'user_request_recurrents.id')
			->select(['user_requests.*', 'user_request_recurrents.repeated as repeated'])
			->with('payment', 'service_type', 'user')
			->first();
		if (!empty($request->repeated)) {
			$dates = json_decode($request->repeated);
			$dateString = '';
			$dayOfWeeks = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
			$dayOfWeeksFr = array("dim", "lun", "mar", "mer", "jeu", "ven", "sam");
			foreach ($dates as $d) {
				$dateString .= "$dayOfWeeksFr[$d], ";
			}
			if (strlen($dateString) >= 2) {
				$dateString = substr($dateString, 0, strlen($dateString) - 2);
			}
			$request->repeatedString = $dateString;
		}

		// echo json_encode($request);
		// exit;

		if ($request) {
			$fleet = Fleet::where('id', $request->fleet_id)->first();
			if (!$fleet) {
				return false;
			}
			$fare = new ServiceTypes();
			$params = array(
				's_latitude' => $request->s_latitude,
				's_longitude' => $request->s_longitude,
				'd_latitude' => $request->d_latitude,
				'd_longitude' => $request->d_longitude,
				'service_type' => $request->service_type_id
			);

			$faredata = $fare->calculateFare($params, 1);
			$faredata = $faredata['data'];
			// echo json_encode($faredata);
			// exit;

			try {
				$data = array(
					'data1' => $fleet->name,
					'data2' => $request->booking_id,
					'data3' => appDateTime($request->schedule_at),
					'data4' => $request->s_address,
					'data5' => $request->d_address,
					'data6' => $request->service_type->name,
					'data7' => $request->note ? $request->note : "",
					'data8' => $request->passenger_name ? $request->passenger_name : "",
					'data9' => $request->repeatedString,
					'data10' => $faredata['time'],
					'data11' => currency($faredata['estimated_fare']),
					'data12' => env('APP_NAME', 'VTCPro'),
					'data13' => env('APP_URL'),
					'data14' => 'info@solutionweb.io',
					'email' => $fleet->email,
				);
				// echo json_encode($data);
				// exit;

				$sid    = config('constants.twilio_sid', '');
				$token  = config('constants.twilio_token', '');
				$client = new Client($sid, $token);

				$number = '+33629957457';
				$number = $fleet->country_code . $fleet->mobile;
				$message = "Bonjour " . $data['data1'] . ", une course vous a été attribuée en date du " . $data['data3'] . "      Pour un départ de " . $data['data4'] . " vers " . $data['data5'] . ".     Veuillez l'accepter avant quelle vous soit retirée.     Merci    " . $data['data13'];
				$client->messages->create(
					$number,
					[
						'from' => config('constants.twilio_from', ''),
						'body' => $message,
					]
				);

				return true;
			} catch ( Throwable $th) {
				// echo json_encode('something went wrong');
				return false;
			}
		} else {
			// echo json_encode('empty booking');
			return false;
		}
	}

	public static function sendFCM($token = '', $msg)
	{
		$pushManager = new PushManager(config('constants.environment') == 'development' ? PushManager::ENVIRONMENT_DEV : PushManager::ENVIRONMENT_PROD);
		$gcmAdapter = new GcmAdapter(array(
			'apiKey' => config('constants.android_push_key'),
		));
		// echo config('constants.android_push_key'); exit;
		$devices = new DeviceCollection(array(
			new Device($token),
		));
		$params = [];
		$message = new Message($msg, $params);
		$push = new Push($gcmAdapter, $devices, $message);
		$pushManager->add($push);
		$pushManager->push();
		foreach ($push->getResponses() as $token => $response) {
			// > $response =
			// Array
			// (
			//     [message_id] => fake_message_id
			//     [multicast_id] => -1
			//     [success] => 1
			//     [failure] => 0
			//     [canonical_ids] => 0
			// )
		}
	}

	public static function welcomeEmailToNewUser($user_type = '', $user = null)
	{
		if ($user_type === '' || $user === null) return false;
		// #1: Logo (as you use for the web voucher)
		// #2: User or Provider or Fleet name
		// #3: Website link like memohi.fr
		// #4: Email like info@solutionweb.io
		// #5: login link (User, Provider and Fleet)
		// #6: I will change the text later write anything
		// #7: Website link like memohi.fr
		// #8: Company name like VTCPro

		// echo json_encode($request);
		// exit;
		try {
			$toEmail = '';
			$subject = 'Inscription';
			$username = '';
			$login_link = '';
			$client_word = '';

			switch ($user_type) {
				case 'provider':
					$toEmail = $user->email;
					$username = $user->first_name . ' ' . $user->last_name;
					$login_link = env('APP_URL') . 'provider/login';
					$client_word = 'Chauffeur';
					break;
				case 'fleet':
					$toEmail = $user->email;
					$username = $user->name;
					$login_link = env('APP_URL') . 'admin';
					$client_word = 'Partenaire';
					break;
				case 'account':
				case 'dispatcher':
				case 'dispute':
					$toEmail = $user->email;
					$username = $user->name;
					$login_link = env('APP_URL') . 'admin';
					break;
				case 'user':
				case 'user-pro':
				default:
					$toEmail = $user->email;
					$username = $user->user_type === 'COMPANY' ? $user->company_name : $user->first_name . ' ' . $user->last_name;
					$login_link = env('APP_URL') . 'login';
					$client_word = 'Client';
					break;
			}


			$data = array(
				'data2' => $username,
				'data3' => env('APP_URL'),
				'data4' => 'info@solutionweb.io',
				'data5' => $login_link,
				'data6' => '',
				'data7' => env('APP_URL'),
				'data8' => env('APP_NAME', 'VTCPro'),
				// 'email' => 'liuwc0026@gmail.com',
				'email' => $toEmail,
				// 'email' => 'w.etisalat@gmail.com',
				'user_type' => $user_type,
				'client_word' => $client_word,
			);
			// echo json_encode($data);
			// exit;

			Mail::send('emails.welcome_register', ['data' => $data], function ($mail) use ($data, $subject) {
				$mail->from($data['data4'], $data['data8']);
				$mail->to($data['email'], 'Weichong Liu')->subject($subject);
			});
			// echo $user['email'];
			return true;
		} catch ( Throwable $th) {
			// echo json_encode('something went wrong');
			return false;
		}
	}

	public static function getGuardName()
	{
		$guard = auth()->guard(); // Retrieve the guard
		$sessionName = $guard->getName(); // Retrieve the session name for the guard
		// The following extracts the name of the guard by disposing of the first
		// and last sections delimited by "_"
		$parts = explode("_", $sessionName);
		unset($parts[count($parts)-1]);
		unset($parts[0]);
		$guardName = implode("_",$parts);

		return $guardName;
	}
	public static function addServiceType()
	{
		$serviceTypeName = ServiceType::where('fleet_id','0')->get();
		$fleetId = Auth::guard('fleet')->id();//get fleet ID
		$originalServiceData = ServiceType::where('fleet_id',0)->get();
		$newServiceData = FleetServiceType::where('fleet_id',$fleetId)->get();
		$newServiceServiceIDs = FleetServiceType::where('fleet_id',$fleetId)->pluck('service_type_id')->toArray();
//		dd($newServiceServiceIDs);
		$data = [] ;
		if(count($originalServiceData) != count($newServiceData))
		{
			foreach ($originalServiceData as $key=>$val)
			{
				if(!in_array($val->id,$newServiceServiceIDs)){
					$data[] = [
						// 'capacity'           => $val->capacity,
						// 'luggage_capacity'   => $val->luggage_capacity,
						'fixed'              => $val->fixed,
						'price'              => $val->price,
						'minute'             => $val->minute,
						'hour'               => $val->hour,
						'distance'           => $val->distance,
						'calculator'         => $val->calculator,
						'description'        => $val->description,
						'waiting_free_mins'  => $val->waiting_free_mins,
						'waiting_min_charge' => $val->waiting_min_charge,
						'fleet_id'           => $fleetId,
						'service_type_id'    => $val->id,
						'status'             => $val->status,
						'created_at'         => Carbon::now()
							->format( 'Y-m-d H:i:s' ),
						'updated_at'         => Carbon::now()
							->format( 'Y-m-d H:i:s' )
					];
				}
			}
			DB::table( 'fleet_service_types' )->insert( $data );
		}
	}
}

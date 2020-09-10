<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\UserRequests;
use App\Helpers\Helper;

class RecurrentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rides:recurrent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a request based on recurrent status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $weekday = ' A.`repeated` LIKE "%' . Carbon::now()->dayOfWeek . '%"'; // weekday[M,T,W...S,Sunday]
        // $weekday = ' A.`repeated` LIKE "%' . Carbon::now()->format('l') . '%"'; // weekday[M,T,W...S,Sunday]
        // $current = " CONCAT('" . \Carbon\Carbon::now()->format("Y-m-d") . " ', TIME(A.`schedule_at`)) < '" . \Carbon\Carbon::now()->addHour(1) . "'";
        // $current2 = " CONCAT('" . \Carbon\Carbon::now()->format("Y-m-d") . " ', TIME(A.`schedule_at`)) > '" . \Carbon\Carbon::now()->subHour(1) . "'";
        $current = " (CONCAT('" . Carbon::now()->format("Y-m-d") . " ', TIME(A.`schedule_at`)) BETWEEN '" . Carbon::now()->subHour(1) . "' AND '" . Carbon::now()->addHour(1) . "')";
        $undeleted = " A.`deleted_at` IS NULL ";
        // $already = " CONCAT('" . \Carbon\Carbon::now()->format("Y-m-d") . " ', TIME(A.`schedule_at`)) > DATE_ADD(B.`assigned_at`, INTERVAL 1 HOUR)";
        // $already2 = " CONCAT('" . \Carbon\Carbon::now()->format("Y-m-d") . " ', TIME(A.`schedule_at`)) < DATE_ADD(B.`assigned_at`, INTERVAL -1 HOUR)";
        $where = " $weekday AND $current AND $undeleted GROUP BY A.id ";
        $query = "SELECT TIME(A.`schedule_at`) as willSchedule, A.* FROM `user_request_recurrents` as A WHERE $where";
        // $query = "SELECT TIME(A.`schedule_at`) as willSchedule, A.* FROM `user_request_recurrents` as A RIGHT JOIN `user_requests` as B ON A.user_id = B.user_id WHERE $where";
        // $UserRequestRecurrent = DB::select(DB::raw($query));
        // $UserRequestRecurrent = UserRequestRecurrent::whereRaw($where)->get();
        $UserRequestRecurrent = [];

        foreach ($UserRequestRecurrent as $key => $data) {
            $willScheduleAt = Carbon::now()->format("Y-m-d") . " " . $data->willSchedule;

            //if there is other requests with same time, don't add new request
            $beforeschedule_time = (new Carbon("$willScheduleAt"))->subHour(1);
			$afterschedule_time = (new Carbon("$willScheduleAt"))->addHour(1);

			$CheckScheduling = UserRequests::where('status','SCHEDULED')
                            ->where('user_id', $data->user_id)
							->whereBetween('schedule_at',[$beforeschedule_time, $afterschedule_time])
                            ->count();
            if ($CheckScheduling > 0) continue;

			$CheckScheduling = UserRequests::whereIn('status',['SCHEDULED', 'SEARCHING', 'ACCEPTED', 'STARTED', 'ARRIVED', 'PICKEDUP', 'DROPPED'])
                            ->where('user_id', $data->user_id)
							->whereBetween('assigned_at',[$beforeschedule_time, $afterschedule_time])
                            ->count();
            if ($CheckScheduling > 0) continue;

            // make a schedule request
            $Req = UserRequests::where("id", $data->user_request_id)->first();
            if (empty($Req)) continue;

            $UserRequest = new UserRequests();

            $UserRequest->booking_id = Helper::generate_booking_id();
            $UserRequest->braintree_nonce = $Req->braintree_nonce;
			$UserRequest->user_id = $Req->user_id;
			$UserRequest->current_provider_id = $Req->current_provider_id;
			$UserRequest->provider_id = $Req->provider_id;
			// $UserRequest->current_provider_id = 0;
			// $UserRequest->provider_id = 0;
			$UserRequest->service_type_id = $Req->service_type_id;
			$UserRequest->rental_hours = $Req->rental_hours;
			$UserRequest->payment_mode = $Req->payment_mode;
			$UserRequest->promocode_id = 0;

			$UserRequest->s_address = $Req->s_address;
			$UserRequest->d_address = $Req->d_address;

			$UserRequest->s_latitude = $Req->s_latitude;
			$UserRequest->s_longitude = $Req->s_longitude;

			$UserRequest->d_latitude = $Req->d_latitude;
			$UserRequest->d_longitude = $Req->d_longitude;
			if($Req->d_latitude == null && $Req->d_longitude == null) {
				$UserRequest->is_drop_location = 0;
			}

			$UserRequest->destination_log = $Req->destination_log;
			$UserRequest->distance = $Req->distance;
			$UserRequest->unit = $Req->unit;
            $UserRequest->use_wallet = $Req->use_wallet;
            $UserRequest->is_track = $Req->is_track;
			$UserRequest->otp = $Req->otp;

			// $UserRequest->assigned_at = null;
			$UserRequest->route_key = $Req->route_key;
            $UserRequest->surge = $Req->surge;
            $UserRequest->status = 'SCHEDULED';
            $UserRequest->schedule_at = $willScheduleAt;
            $UserRequest->is_scheduled = 'YES';

            $UserRequest->passenger_name = $Req->passenger_name;
            $UserRequest->passenger_phone = $Req->passenger_phone;
            $UserRequest->comment = $Req->comment;

            $UserRequest->user_req_recurrent_id = $data->id;

            $UserRequest->save();

            // send push notification

        }

        // $results = DB::select( DB::raw("SELECT * FROM user_request_recurrents WHERE repeated LIKE '%\"".Carbon::now()->format('l')."\"' AND CONCAT('".\Carbon\Carbon::now()->format('Y-m-d')."', time('schedule_at')) <= '".\Carbon\Carbon::now()->addHour()."'") );
        // UserRequestRecurrent::where('schedule_at','<=',\Carbon\Carbon::now()->addMinutes(20));
        // var_dump(Carbon::now()->format('l'));
        // echo json_encode($UserRequestRecurrent);
        // echo json_encode($UserRequestRecurrent);
        // echo json_encode($query);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\UserRequests;
use function GuzzleHttp\json_encode;

class AutoCancelAssignCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rides:auto_cancel_assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel ride which assigned to driver and fleet by admin';

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
        $UserRequestsAssigned = UserRequests::where('status','SCHEDULED')->whereNotNull('manual_assigned_at')->get();
        $now = Carbon::now();
        foreach ($UserRequestsAssigned as $request) {
            $expireDate = $request->manual_assigned_at->addHours($request->timeout);
            $diff = $now->diffInSeconds($expireDate, false);
            if ($diff <= 0) {
                UserRequests::where('id', $request->id)->update(['provider_id' => 0, 'current_provider_id' => 0, 'manual_assigned_at' => null, 'timeout'=>0]);
            }
        }
    }
}

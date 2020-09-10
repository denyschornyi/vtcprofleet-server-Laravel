<?php

namespace App\Console\Commands;

use App\Pool;
use App\UserRequests;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelPoolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:cancelpool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When time is over, cancel the ride of pool';

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
	    $cancelIds = Pool::where('expire_date','<=',Carbon::now()->format('Y-m-d H:i:s'))->pluck('request_id')->toArray();

	    UserRequests::whereIn( 'id', $cancelIds )->update( [
		    'provider_id'         => 0,
		    'current_provider_id' => 0,
		    'manual_assigned_at'  => null,
		    'timeout'             => 0,
	    ] );

	    Pool::whereIn('request_id',$cancelIds)->delete();
    }
}

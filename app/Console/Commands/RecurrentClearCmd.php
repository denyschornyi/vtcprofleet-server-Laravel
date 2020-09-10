<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\UserRequestRecurrent;
use DB;
use function GuzzleHttp\json_encode;

class RecurrentClearCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurrent:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all empty or deleted recurrents';

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
        // delete 
        // UserRequestRecurrent::whereIn('repeated', [json_encode([]), ""])->delete();
        // DB::delete('delete from user_request_recurrents where deleted_at IS NOT NULL');
    }
}

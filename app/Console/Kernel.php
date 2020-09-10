<?php

namespace App\Console;

use App\Console\Commands\AutoCancelAssignCommand;
use App\Console\Commands\CustomCommand;
use App\Console\Commands\DbClearCommand;
use App\Console\Commands\ProviderCommand;
use App\Console\Commands\RecurrentCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CustomCommand::class,
        DbClearCommand::class,
        ProviderCommand::class,
        RecurrentCommand::class,
        AutoCancelAssignCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cronjob:rides')
                ->everyMinute();

        $schedule->command('cronjob:providers')
                ->everyFiveMinutes();

        // $schedule->command('cronjob:demodata')
        //         ->weeklyOn(1, '8:00');

        $schedule->call('App\Http\Controllers\AdminController@DBbackUp')->everyMinute();

        $schedule->command('rides:recurrent')
                ->everyMinute();

        $schedule->command('rides:auto_cancel_assign')
                ->everyMinute();

        $schedule->command('cronjob:cancelpool')->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

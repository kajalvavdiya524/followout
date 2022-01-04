<?php

namespace App\Console;

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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('subscriptions:update')->everyMinute()->withoutOverlapping();
        $schedule->command('videos:update')->everyMinute()->withoutOverlapping();
        $schedule->command('followouts:update-default')->daily()->withoutOverlapping();
        $schedule->command('users:release-expired-roles')->everyMinute()->withoutOverlapping();

        // Some commands should not run on local env
        if (app()->isLocal()) {
            $schedule->command('tokens:clear')->daily()->withoutOverlapping();
        } else {
            $schedule->command('tokens:clear')->everyMinute()->withoutOverlapping();
            $schedule->command('followouts:notify-about-expiring')->everyMinute()->withoutOverlapping();
            $schedule->command('payouts:update')->everyMinute()->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

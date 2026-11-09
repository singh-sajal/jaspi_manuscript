<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('queue:work --stop-when-empty --queue=default --tries=3')->everyMinute()->withoutOverlapping();

        // Refresh per-reviewer deadline tracking and email handling editors
        // about reviewers that have passed their timeout deadline.
        $schedule->command('reviewers:sync-deadlines')->everyTenMinutes()->withoutOverlapping();

        // Keep the notifications table from growing indefinitely.
        $schedule->command('notifications:prune --days=90')->dailyAt('02:00')->withoutOverlapping();
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

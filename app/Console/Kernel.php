<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ClearRequestCache::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        \Log::channel('commands')->info('Schedule run...');
    //     $schedule->call(function () {
    //         \Log::channel('commands')->info('Running schedule every minute');
    //       })->everyMinute();
    //    //$schedule->command('app:clear-request-cache')->hourly();
    //     $schedule->command('app:clear-request-cache')->everyMinute();
        \Log::channel('commands')->info('Schedule end.');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

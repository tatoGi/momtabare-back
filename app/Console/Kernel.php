<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Check BOG payments status every 5 minutes
        $schedule->command('bog:check-payments')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CheckBogPayments::class,
    ];

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

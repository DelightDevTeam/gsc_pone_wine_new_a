<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('summary:fetch')->dailyAt('00:01');
        //$schedule->command('result:delete-old-backups {start_date} {end_date}');
        // Schedule the deletion of old results with static dates
        $startDate = '2024-11-18';
        $endDate = '2024-11-19';
        $schedule->command("result:delete-old-backups $startDate $endDate")->dailyAt('00:30');

        $sDate = '2024-11-18';
        $eDate = '2024-11-19';
        $schedule->command("bet:delete-old-backups $sDate $eDate")->dailyAt('00:30');

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

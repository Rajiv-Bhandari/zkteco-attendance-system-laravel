<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $pullTimes = DB::table('ct_hr_attendance_device_thumbLog_pulltime')
                    ->pluck('pull_time');

        foreach ($pullTimes as $time) 
        {
            $schedule->call(function () 
            {
                Artisan::call('attendance:sync');
            })->dailyAt($time);
        }
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncAttendance extends Command
{
    protected $signature = 'attendance:sync';

    protected $description = 'Sync attendance from ZKTeco devices';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $currentTime = Carbon::now()->format('H:i:00');

        $pullTimes = DB::table('ct_hr_attendance_device_thumbLog_pulltime')
            ->pluck('pull_time')
            ->toArray();

        Log::info('Scheduler running at: ' . $currentTime);

        if (in_array($currentTime, $pullTimes)) 
        {
            Log::info('Executing attendance sync at ' . $currentTime);

            try 
            {
                $controller = new AttendanceController();
                $controller->attendance();
                Log::info('Attendance sync completed successfully.');
                $this->info('Attendance sync completed at ' . $currentTime);
            } 
            catch (\Exception $e) 
            {
                Log::error('Attendance sync failed: ' . $e->getMessage());
                $this->error('Attendance sync failed: ' . $e->getMessage());
            }
        } 
        else 
        {
            Log::info('No sync scheduled at this time.');
        }
    }
}
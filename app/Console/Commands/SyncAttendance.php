<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AttendanceController;

class SyncAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance from ZKTeco devices';

    /**
     * Execute the console command.
     */

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $controller = new AttendanceController();
        $controller->attendance();
    }
}

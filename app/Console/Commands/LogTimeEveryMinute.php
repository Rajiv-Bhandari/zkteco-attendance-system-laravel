<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LogTimeEveryMinute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:time-every-minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log the current time every minute';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTime = Carbon::now()->toDateTimeString();
        Log::info("Current time: " . $currentTime);
        $this->info("Time logged: " . $currentTime);
    }
}

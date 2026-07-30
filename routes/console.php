<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily route stop generation (currently everyMinute for cron testing)
use Illuminate\Support\Facades\Schedule;
Schedule::command('routes:generate')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

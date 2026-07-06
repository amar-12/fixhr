<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();



// Schedule::command('app:update-attendance-summary')
//                     ->everyFiveMinutes();

// Schedule::command('app:monthly-leave-balance-cron')->everyFiveMinutes();
Schedule::command('app:monthly-leave-balance-cron')->monthly()->at('00:00');
Schedule::command('app:employee-separation')->dailyAt('00:01');
Schedule::command('app:manager-apply-change')->dailyAt('00:01')->withoutOverlapping()->onOneServer();
Schedule::command('app:report-attendance')->dailyAt('11:10');
Schedule::command('app:birthday:reminder')->dailyAt('00:01');
Schedule::command('app:missed-punch-late-check')->dailyAt('00:01');

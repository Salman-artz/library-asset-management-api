<?php

use App\Jobs\SendBorrowingReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run borrowing reminders daily at 8:00 AM
Schedule::job(new SendBorrowingReminders)->dailyAt('08:00');

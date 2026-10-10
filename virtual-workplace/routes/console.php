<?php

use App\Domains\Notifications\Models\WorkplaceNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tasks:process-recurring')->hourly();
Schedule::command('tasks:due-reminders')->hourly();
// Old notifications (see WorkplaceNotification::prunable) are cleared nightly.
Schedule::command('model:prune', ['--model' => [WorkplaceNotification::class]])->daily();

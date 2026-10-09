<?php

namespace App\Support;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Tenancy\Models\Organization;
use App\Mail\TaskActivityMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function Illuminate\Support\defer;

/**
 * Sends the email copy of a task notification when the recipient wants it.
 * Web requests send after the response is flushed so they never wait on SMTP;
 * the scheduler (due reminders) sends inline.
 */
class TaskMailDispatcher
{
    public static function queue(WorkplaceNotification $notification): void
    {
        if (! array_key_exists($notification->type, NotificationPreferences::TYPE_TO_KEY)) {
            return;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            self::send($notification);

            return;
        }

        defer(fn () => self::send($notification));
    }

    public static function send(WorkplaceNotification $notification): void
    {
        $user = User::find($notification->user_id);
        if (! $user || ! $user->email || ! NotificationPreferences::wantsEmail($user, $notification->type)) {
            return;
        }

        try {
            OrganizationMailer::apply($notification->organization_id ? Organization::find($notification->organization_id) : null);
            Mail::to($user->email)->send(new TaskActivityMail(
                $notification,
                $user->name,
                url($notification->action_url ?: '/dashboard'),
            ));
        } catch (\Throwable $e) {
            Log::warning("Task email to {$user->email} failed: ".$e->getMessage());
        }
    }
}

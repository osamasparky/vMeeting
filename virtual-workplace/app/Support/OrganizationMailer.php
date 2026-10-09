<?php

namespace App\Support;

use App\Domains\Tenancy\Models\Organization;
use Illuminate\Support\Facades\Config;

/**
 * Point the mailer at an organization's own SMTP server (Settings → SMTP) when
 * one is configured; otherwise the application mailer is used unchanged.
 */
class OrganizationMailer
{
    public static function apply(?Organization $organization): void
    {
        if (! $organization) {
            return;
        }

        $smtp = $organization->settings?->smtp_settings ?? [];
        // Settings → SMTP saves mail_* keys; the bare names are read too for rows saved before that.
        $get = fn (string $key) => $smtp['mail_'.$key] ?? $smtp[$key] ?? null;
        if (empty($get('host'))) {
            return;
        }

        $encryption = $get('encryption') ?? 'tls';
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $get('host'));
        Config::set('mail.mailers.smtp.port', (int) ($get('port') ?? 587));
        Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
        Config::set('mail.mailers.smtp.username', $get('username'));
        Config::set('mail.mailers.smtp.password', $get('password'));
        Config::set('mail.from.address', $get('from_address') ?? config('mail.from.address'));
        Config::set('mail.from.name', $get('from_name') ?? $organization->name);
    }
}

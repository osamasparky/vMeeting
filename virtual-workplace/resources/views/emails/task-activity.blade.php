@php
    // Colours come from the design-system tokens (EmailTokens), never literals:
    // mail clients can't read CSS custom properties.
    $rtl = app()->getLocale() === 'ar';
    $page = $c['surface/page'] ?? 'inherit';
    $card = $c['surface/card'] ?? 'inherit';
    $ink = $c['text/primary'] ?? 'inherit';
    $muted = $c['text/secondary'] ?? 'inherit';
    $accent = $c['accent/default'] ?? 'inherit';
    $onAccent = $c['accent/fg'] ?? 'inherit';
    $rule = $c['border/subtle'] ?? 'inherit';
    $gold = $c['highlight/default'] ?? $accent;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
</head>
<body style="margin:0;padding:0;background:{{ $page }};font-family:'Cairo','IBM Plex Sans Arabic','Segoe UI',Tahoma,sans-serif;color:{{ $ink }};">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $page }};padding:24px 12px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:{{ $card }};border:1px solid {{ $rule }};border-radius:14px;">
                <tr><td style="padding:20px 24px;border-bottom:3px solid {{ $gold }};text-align:{{ $rtl ? 'right' : 'left' }};">
                    <div style="font-size:13px;font-weight:700;color:{{ $muted }};">UlaSpace</div>
                </td></tr>
                <tr><td style="padding:24px;text-align:{{ $rtl ? 'right' : 'left' }};">
                    <p style="margin:0 0 6px;font-size:14px;color:{{ $muted }};">{{ __('pm.mail_hello', ['name' => $recipientName]) }}</p>
                    <h1 style="margin:0 0 12px;font-size:20px;line-height:1.4;color:{{ $ink }};">{{ $notification->title }}</h1>
                    @if($notification->body)
                        <p style="margin:0 0 20px;font-size:15px;line-height:1.7;color:{{ $ink }};">{{ $notification->body }}</p>
                    @endif
                    <a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 22px;border-radius:10px;background:{{ $accent }};color:{{ $onAccent }};font-weight:700;font-size:14px;text-decoration:none;">{{ __('pm.mail_open_task') }}</a>
                </td></tr>
                <tr><td style="padding:16px 24px;border-top:1px solid {{ $rule }};font-size:12px;color:{{ $muted }};text-align:{{ $rtl ? 'right' : 'left' }};">
                    {{ __('pm.mail_footer') }}
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>

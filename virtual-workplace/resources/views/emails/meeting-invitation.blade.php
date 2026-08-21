<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #F5F3E8;
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #192D21;
            line-height: 1.6;
        }
        .email-wrapper {
            width: 100%;
            background-color: #F5F3E8;
            padding: 30px 10px;
        }
        .email-card {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFDF6;
            border: 1px solid #D5DED0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(36, 92, 58, 0.08);
        }
        .email-header {
            background: linear-gradient(135deg, #1C4D30 0%, #245C3A 100%);
            padding: 30px 24px;
            text-align: center;
            color: #FFFDF6;
        }
        .email-header h1 {
            margin: 0 0 6px 0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .email-header p {
            margin: 0;
            font-size: 13px;
            color: #A8C4A0;
        }
        .email-body {
            padding: 30px 24px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-project {
            background: #E8EFE2;
            color: #245C3A;
            border: 1px solid #C8D8BE;
        }
        .badge-general {
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #FDE68A;
        }
        .meeting-box {
            background: #F5F3E8;
            border: 1px solid #D5DED0;
            border-radius: 14px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #D5DED0;
            font-size: 13px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #637567;
            font-weight: 700;
        }
        .info-val {
            font-weight: 800;
            color: #192D21;
        }
        .btn-cta {
            display: block;
            text-align: center;
            background: #245C3A;
            color: #FFFDF6 !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 900;
            font-size: 15px;
            margin: 26px auto 10px auto;
            box-shadow: 0 4px 0 #183F27, 0 8px 20px rgba(36, 92, 58, 0.25);
        }
        .email-footer {
            text-align: center;
            padding: 20px 24px;
            font-size: 11px;
            color: #8C9C8F;
            border-top: 1px solid #D5DED0;
            background: #FAF8F0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-card">
            <div class="email-header">
                <div style="font-size: 32px; margin-bottom: 8px;">📅</div>
                <h1>{{ __('Meeting Invitation') }}</h1>
                <p>{{ $meeting->organization->name ?? 'Virtual Workplace' }}</p>
            </div>

            <div class="email-body">
                <p style="font-size: 15px; font-weight: 700; margin-top: 0;">
                    {{ __('Hello') }} {{ $recipient->name }},
                </p>
                <p style="font-size: 13px; color: #4A5B4E;">
                    {{ __('You have been invited to a scheduled meeting by') }} <strong>{{ $meeting->creator->name ?? 'Meeting Organizer' }}</strong>.
                </p>

                <div style="margin: 16px 0 10px 0;">
                    @if($meeting->project)
                        <span class="badge badge-project">📁 {{ $meeting->project->name }} ({{ $meeting->project->code }})</span>
                    @else
                        <span class="badge badge-general">🌐 {{ __('General Meeting') }}</span>
                    @endif
                </div>

                <div class="meeting-box">
                    <h2 style="margin: 0 0 14px 0; font-size: 18px; color: #245C3A; font-weight: 900;">
                        {{ $meeting->title }}
                    </h2>

                    @if($meeting->description)
                        <p style="font-size: 12px; color: #637567; margin: 0 0 14px 0; font-style: italic;">
                            "{{ $meeting->description }}"
                        </p>
                    @endif

                    <div class="info-row">
                        <span class="info-label">🗓️ {{ __('Date & Time') }}</span>
                        <span class="info-val">
                            {{ $meeting->scheduled_at ? $meeting->scheduled_at->format('Y-m-d H:i (T)') : __('To be announced') }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">⏱️ {{ __('Duration') }}</span>
                        <span class="info-val">{{ $meeting->duration_minutes ?? 30 }} {{ __('Minutes') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">🚪 {{ __('Meeting Room') }}</span>
                        <span class="info-val">{{ $meeting->room->name ?? __('Main Workplace Conference Room') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">👤 {{ __('Host / Organizer') }}</span>
                        <span class="info-val">{{ $meeting->creator->name ?? 'Admin' }}</span>
                    </div>
                </div>

                <a href="{{ $joinUrl }}" class="btn-cta" target="_blank">
                    🚀 {{ __('Join Scheduled Meeting') }}
                </a>

                <p style="font-size: 11px; color: #8C9C8F; text-align: center; margin-top: 14px;">
                    {{ __('You will also receive a sound chime and desktop alert on your dashboard right before the session begins.') }}
                </p>
            </div>

            <div class="email-footer">
                © {{ date('Y') }} {{ $meeting->organization->name ?? 'vMeeting Virtual Workplace' }}. {{ __('All rights reserved.') }}
            </div>
        </div>
    </div>
</body>
</html>

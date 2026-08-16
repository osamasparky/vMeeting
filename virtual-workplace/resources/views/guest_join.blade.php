<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Invitation — Virtual Workplace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #070913;
            --bg-card: rgba(15, 23, 42, 0.85);
            --border-card: rgba(255, 255, 255, 0.08);
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --accent-glow: rgba(99, 102, 241, 0.35);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.12) 0%, transparent 40%);
            padding: 20px;
        }

        .lobby-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(20px);
            text-align: center;
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px var(--accent-glow);
        }

        .title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .room-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 14px 16px;
            color: var(--text-main);
            font-size: 15px;
            outline: none;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 15px var(--accent-glow);
            background: rgba(255, 255, 255, 0.08);
        }

        .join-btn {
            width: 100%;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 15px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 10px 25px var(--accent-glow);
        }

        .join-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px var(--accent-glow);
        }

        .error-card {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="lobby-card">
        <div class="brand-icon">🌐</div>

        @if(!empty($error))
            <h1 class="title">Invitation Issue</h1>
            <div class="error-card">{{ $error }}</div>
            <a href="{{ route('login') }}" class="join-btn" style="text-decoration: none;">
                Go to Homepage
            </a>
        @else
            <h1 class="title">{{ $invitation->organization->name }}</h1>
            <p class="subtitle">
                You have been invited by <strong>{{ $invitation->host->name }}</strong> to join their virtual office space.
            </p>

            <div class="room-badge">
                <span>🏢</span>
                <span>Destination Room: <strong>{{ $invitation->room->name }}</strong></span>
            </div>

            <form action="{{ route('guest.enter', $invitation->token) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Your Full Name (Display Name)</label>
                    <input type="text" name="guest_name" class="form-input" value="{{ old('guest_name', $invitation->guest_name) }}" required placeholder="e.g. John Smith / Partner">
                </div>

                <button type="submit" class="join-btn">
                    <span>🚀</span> Enter Workplace as Guest
                </button>
            </form>
        @endif
    </div>

</body>
</html>

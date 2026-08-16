<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organization->name }} — Guest Virtual Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #070913;
            --bg-panel: rgba(15, 23, 42, 0.92);
            --border-panel: rgba(255, 255, 255, 0.08);
            --accent: #10b981;
            --accent-glow: rgba(16, 185, 129, 0.35);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            user-select: none;
        }

        .office-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border-panel);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 15px;
        }

        .guest-badge {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .room-indicator {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-panel);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            color: #cbd5e1;
        }

        canvas#office-canvas {
            display: block;
            width: 100vw;
            height: 100vh;
        }

        .chat-drawer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 320px;
            height: 380px;
            background: var(--bg-panel);
            border: 1px solid var(--border-panel);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(16px);
        }

        .chat-header {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-panel);
            font-size: 13px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            padding: 12px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 12px;
        }

        .message-bubble {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-panel);
            padding: 8px 10px;
            border-radius: 8px;
        }

        .message-sender {
            font-weight: 700;
            font-size: 11px;
            color: #a5b4fc;
            margin-bottom: 2px;
        }

        .chat-input-bar {
            padding: 10px;
            border-top: 1px solid var(--border-panel);
            display: flex;
            gap: 6px;
        }

        .chat-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-panel);
            border-radius: 8px;
            padding: 8px 12px;
            color: white;
            font-size: 12px;
            outline: none;
        }

        .chat-send-btn {
            background: var(--accent);
            border: none;
            color: black;
            font-weight: 700;
            padding: 0 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="office-header">
        <div class="header-left">
            <div class="brand-pill">
                <span>🌐</span>
                <span>{{ $organization->name }}</span>
            </div>
            <span class="guest-badge">GUEST ACCESS</span>
            <div class="room-indicator" id="current-room-name">📍 {{ $room->name }}</div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="font-size: 12px; color: var(--text-muted);">
                Logged in as: <strong style="color: white;">{{ $guestUser['name'] }}</strong>
            </div>
        </div>
    </header>

    <!-- Interactive Canvas -->
    <canvas id="office-canvas"></canvas>

    <!-- Chat Drawer -->
    <div class="chat-drawer">
        <div class="chat-header">
            <span>💬 Office Chat</span>
            <span style="font-size: 11px; color: #10b981;">● Online</span>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message-bubble" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2);">
                <div class="message-sender" style="color: #6ee7b7;">System</div>
                Welcome {{ $guestUser['name'] }}! You joined as a guest in {{ $room->name }}. Move with WASD, Arrow keys, or Click anywhere.
            </div>
        </div>
        <form class="chat-input-bar" id="chat-form">
            <input type="text" class="chat-input" id="chat-input" placeholder="Type a message..." autocomplete="off">
            <button type="submit" class="chat-send-btn">Send</button>
        </form>
    </div>

    <script>
        const CONFIG = {
            wsUrl: "{{ $wsUrl }}",
            token: "{{ $realtimeToken }}",
            map: @json($map),
            guestUser: @json($guestUser),
            invitedRoom: @json($room)
        };

        const canvas = document.getElementById('office-canvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        });

        // Initial spawn in invited room
        const spawnX = (CONFIG.invitedRoom.bounds.x + CONFIG.invitedRoom.bounds.width / 2) * 32;
        const spawnY = (CONFIG.invitedRoom.bounds.y + CONFIG.invitedRoom.bounds.height / 2) * 32;

        const localAvatar = {
            id: CONFIG.guestUser.id,
            name: CONFIG.guestUser.name,
            x: spawnX,
            y: spawnY,
            targetX: spawnX,
            targetY: spawnY,
            speed: 4.5,
            radius: 18,
            color: '#10B981',
            direction: 'down',
            proximityRadius: 150,
            currentRoom: CONFIG.invitedRoom
        };

        const remoteAvatars = new Map();
        const rooms = CONFIG.map.rooms || [];
        const zones = CONFIG.map.zones || [];

        let ws = null;
        function connectWebSocket() {
            try {
                ws = new WebSocket(`${CONFIG.wsUrl}?token=${CONFIG.token}`);
                ws.onopen = () => {
                    ws.send(JSON.stringify({
                        type: 'map.join',
                        payload: {
                            mapId: CONFIG.map.id,
                            initialPosition: { x: localAvatar.x, y: localAvatar.y }
                        }
                    }));
                };
                ws.onmessage = (e) => {
                    try {
                        const event = JSON.parse(e.data);
                        if (event.type === 'welcome') {
                            (event.payload.occupants || []).forEach(u => {
                                remoteAvatars.set(u.userId, { id: u.userId, name: u.name, x: u.position.x, y: u.position.y, targetX: u.position.x, targetY: u.position.y, color: '#6366F1' });
                            });
                        } else if (event.type === 'user.joined') {
                            const u = event.payload;
                            remoteAvatars.set(u.userId, { id: u.userId, name: u.name, x: u.position.x, y: u.position.y, targetX: u.position.x, targetY: u.position.y, color: '#6366F1' });
                            addChatMessage('System', `${u.name} stepped in.`, '#10B981');
                        } else if (event.type === 'user.left') {
                            remoteAvatars.delete(event.payload.userId);
                        } else if (event.type === 'position.updated') {
                            const av = remoteAvatars.get(event.payload.userId);
                            if (av) { av.targetX = event.payload.position.x; av.targetY = event.payload.position.y; }
                        } else if (event.type === 'chat.message') {
                            addChatMessage(event.payload.senderName, event.payload.body);
                        }
                    } catch (err) { console.error(err); }
                };
            } catch (err) { console.error(err); }
        }
        connectWebSocket();

        // ── Controls ──
        const keys = {};
        window.addEventListener('keydown', (e) => {
            if (['w', 'a', 's', 'd', 'ArrowUp', 'ArrowLeft', 'ArrowDown', 'ArrowRight'].includes(e.key)) {
                if (document.activeElement !== document.getElementById('chat-input')) {
                    keys[e.key.toLowerCase()] = true;
                }
            }
        });
        window.addEventListener('keyup', (e) => { keys[e.key.toLowerCase()] = false; });
        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            localAvatar.targetX = e.clientX - rect.left;
            localAvatar.targetY = e.clientY - rect.top;
        });

        // ── Loop ──
        let lastBroadcast = 0;
        function update() {
            let dx = 0, dy = 0, moved = false;
            if (keys['w'] || keys['arrowup']) dy -= 1;
            if (keys['s'] || keys['arrowdown']) dy += 1;
            if (keys['a'] || keys['arrowleft']) dx -= 1;
            if (keys['d'] || keys['arrowright']) dx += 1;

            if (dx !== 0 || dy !== 0) {
                const len = Math.sqrt(dx*dx + dy*dy);
                localAvatar.x += (dx/len) * localAvatar.speed;
                localAvatar.y += (dy/len) * localAvatar.speed;
                localAvatar.targetX = localAvatar.x;
                localAvatar.targetY = localAvatar.y;
                moved = true;
            } else {
                const tx = localAvatar.targetX - localAvatar.x;
                const ty = localAvatar.targetY - localAvatar.y;
                const dist = Math.sqrt(tx*tx + ty*ty);
                if (dist > 3) {
                    localAvatar.x += (tx/dist) * localAvatar.speed;
                    localAvatar.y += (ty/dist) * localAvatar.speed;
                    moved = true;
                }
            }

            localAvatar.x = Math.max(localAvatar.radius, Math.min(width - localAvatar.radius, localAvatar.x));
            localAvatar.y = Math.max(localAvatar.radius + 70, Math.min(height - localAvatar.radius, localAvatar.y));

            remoteAvatars.forEach(rem => {
                rem.x += (rem.targetX - rem.x) * 0.2;
                rem.y += (rem.targetY - rem.y) * 0.2;
            });

            const now = Date.now();
            if (moved && now - lastBroadcast > 65 && ws && ws.readyState === WebSocket.OPEN) {
                lastBroadcast = now;
                ws.send(JSON.stringify({ type: 'position.update', payload: { x: Math.round(localAvatar.x), y: Math.round(localAvatar.y) } }));
            }
        }

        function draw() {
            ctx.clearRect(0, 0, width, height);
            const tileSize = 32;

            // Grid
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.03)';
            for (let x = 0; x < width; x += tileSize) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, height); ctx.stroke(); }
            for (let y = 0; y < height; y += tileSize) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(width, y); ctx.stroke(); }

            // Rooms
            rooms.forEach(r => {
                const rx = r.bounds.x * tileSize;
                const ry = r.bounds.y * tileSize;
                const rw = r.bounds.width * tileSize;
                const rh = r.bounds.height * tileSize;
                ctx.fillStyle = 'rgba(30, 41, 59, 0.6)';
                ctx.fillRect(rx, ry, rw, rh);
                ctx.strokeStyle = r.color || '#6366F1';
                ctx.lineWidth = 2;
                ctx.strokeRect(rx, ry, rw, rh);

                ctx.fillStyle = '#f8fafc';
                ctx.font = '600 12px Inter';
                ctx.fillText(`🏢 ${r.name}`, rx + 14, ry + 25);
            });

            // Objects
            const OBJECT_CONFIGS = { desk: '💻', chair: '🪑', sofa: '🛋️', whiteboard: '📋', plant: '🪴', screen: '📺', wall: '🧱', door: '🚪', pingpong: '🏓', water_cooler: '🚰', bookshelf: '📚', cabinet: '🗄️', dining_table: '🍽️', lamp: '💡' };
            (CONFIG.map.objects || []).forEach(obj => {
                const ox = obj.position.x * tileSize;
                const oy = obj.position.y * tileSize;
                ctx.fillStyle = 'rgba(255, 255, 255, 0.07)';
                ctx.fillRect(ox + 2, oy + 2, tileSize - 4, tileSize - 4);
                ctx.font = '18px Inter';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(OBJECT_CONFIGS[obj.type] || '📦', ox + tileSize/2, oy + tileSize/2);
                ctx.textAlign = 'start';
                ctx.textBaseline = 'alphabetic';
            });

            // Remote avatars
            remoteAvatars.forEach(av => {
                ctx.beginPath(); ctx.arc(av.x, av.y, 18, 0, Math.PI * 2); ctx.fillStyle = av.color; ctx.fill();
                ctx.fillStyle = '#fff'; ctx.font = 'bold 12px Inter'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                ctx.fillText(av.name.charAt(0).toUpperCase(), av.x, av.y);
                ctx.font = '11px Inter'; ctx.fillText(av.name, av.x, av.y - 24);
                ctx.textAlign = 'start'; ctx.textBaseline = 'alphabetic';
            });

            // Local guest avatar
            ctx.beginPath(); ctx.arc(localAvatar.x, localAvatar.y, 18, 0, Math.PI * 2); ctx.fillStyle = '#10B981'; ctx.fill();
            ctx.strokeStyle = '#fff'; ctx.lineWidth = 2.5; ctx.stroke();
            ctx.fillStyle = '#fff'; ctx.font = 'bold 12px Inter'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.fillText(localAvatar.name.charAt(0).toUpperCase(), localAvatar.x, localAvatar.y);
            ctx.font = '600 11px Inter'; ctx.fillText(`${localAvatar.name} (You)`, localAvatar.x, localAvatar.y - 24);
            ctx.textAlign = 'start'; ctx.textBaseline = 'alphabetic';

            requestAnimationFrame(() => { update(); draw(); });
        }
        draw();

        function addChatMessage(sender, body, color) {
            const container = document.getElementById('chat-messages');
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.innerHTML = `<div class="message-sender" style="color: ${color || '#a5b4fc'}">${sender}</div><div>${body}</div>`;
            container.appendChild(bubble);
            container.scrollTop = container.scrollHeight;
        }

        document.getElementById('chat-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            if (text && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'chat.send', payload: { channelId: 'general', body: text } }));
                addChatMessage(`${localAvatar.name} (You)`, text, '#6ee7b7');
                input.value = '';
            }
        });
    </script>
</body>
</html>

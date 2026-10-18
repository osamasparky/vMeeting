/**
 * Office arcade — the client half of the game machines.
 *
 * Press E at a machine: its window opens (lobby), you can watch, take a seat,
 * and the first seated player starts. During a match the realtime server
 * sends a frame ~30×/s and this file draws it; we only send our held keys
 * (or, for pool, the shot). Rules and physics live on the server
 * (realtime/src/games), so everyone sees the same match.
 *
 * window.UlaGames = { init, open, receive, capturesKeys, rejoin }
 */
(function () {
    'use strict';

    const FIELDS = {
        snipers: { w: 960, h: 540 },
        racing: { w: 1000, h: 620 },
        karts: { w: 900, h: 600 },
        pool: { w: 1000, h: 560 },
        run: { w: 840, h: 520 }, // 21 × 13 tiles of 40 px
        // Drawn with HTML, not on the canvas:
        quiz: null,
        chess: null,
        tictactoe: null,
    };

    // Player colours come from the design tokens; names are always drawn too, so colour is never the only cue.
    const PLAYER_TOKENS = [
        '--ula-tone-palm-dot', '--ula-tone-gold-dot', '--ula-tone-terracotta-dot', '--ula-status-info',
        '--ula-status-danger', '--ula-tone-stone-dot', '--ula-status-warning', '--ula-status-success',
    ];

    // Game art (scenery, pool balls) has fixed colours, like the furniture images.
    const ART = {
        sky1: '#1b3223', sky2: '#3c6b4c', building: '#4a443c', window: '#e6c88b', roof: '#857a6c',
        grass: '#2a5439', grass2: '#1e412f', asphalt: '#4a443c', kerbA: '#f9f6ef', kerbB: '#b46c34', line: '#e6c88b',
        void: '#0e1c17', platform: '#ede6d9', platformEdge: '#b98a37', box: '#d3a553',
        rail: '#7d451d', cloth: '#2a5439', pocket: '#0e1c17', white: '#f9f6ef', ink: '#0e1c17',
        ball: ['#f9f6ef', '#e3b505', '#1d4e89', '#c0392b', '#5b2c83', '#e67e22', '#1e8449', '#7b241c', '#0e1c17'],
    };

    let send = () => {};
    let csrf = '';
    let me = { id: '', name: '' };
    let T = {};
    let rtl = false;

    let machine = null; // { id, game, name }
    let state = null; // last game.state
    let meta = null;
    let frame = null;
    let prev = null;
    let frameAt = 0;
    let prevAt = 0;
    let raf = 0;

    const held = { up: false, down: false, left: false, right: false, a: false, b: false };
    let lastSent = '';
    const aim = { angle: Math.PI, charging: false, chargeStart: 0, mouse: null };

    const $ = (id) => document.getElementById(id);
    const tok = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    const t = (key, rep) => {
        let s = T[key] || key;
        if (rep) for (const k in rep) s = s.replace(':' + k, rep[k]);
        return s;
    };
    const colorOf = (slot) => tok(PLAYER_TOKENS[slot % PLAYER_TOKENS.length]) || 'gray';

    // ── public API ──

    function init(opts) {
        send = opts.send;
        csrf = opts.csrf || '';
        me = opts.me;
        T = opts.text || {};
        rtl = document.documentElement.dir === 'rtl';

        $('game-close').addEventListener('click', close);
        $('game-modal').addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { e.preventDefault(); close(); }
        });
        window.addEventListener('keydown', onKey, true);
        window.addEventListener('keyup', onKey, true);
        window.addEventListener('blur', releaseAll);
        window.addEventListener('resize', () => machine && fitCanvas());

        const cv = $('game-canvas');
        cv.addEventListener('mousemove', (e) => { aim.mouse = toField(e); });
        cv.addEventListener('mousedown', (e) => {
            if (machine?.game === 'pool') { aim.mouse = toField(e); startCharge(); }
            else if (machine?.game === 'snipers') { setKey('a', true); }
        });
        window.addEventListener('mouseup', () => {
            if (machine?.game === 'pool' && aim.charging) shoot();
            else if (machine?.game === 'snipers') setKey('a', false);
        });
    }

    function open(m) {
        if (!m || !(m.game in FIELDS)) return;
        if (machine && machine.id !== m.id) close();
        machine = m;
        state = null;
        frame = prev = meta = null;
        $('game-modal').hidden = false;
        $('game-title-ar').textContent = t('games.name.' + m.game + '.ar');
        $('game-title-en').textContent = '⁦' + t('games.name.' + m.game + '.en') + '⁩';
        $('game-help').textContent = t('games.help.' + m.game);
        $('game-close').focus();
        quiz.pick = null;
        quiz.error = '';
        quiz.myPick = null;
        renderLobby();
        if (m.game === 'quiz') loadQuizzes();
        send({ type: 'game.open', payload: { machineId: m.id, game: m.game } });
    }

    function close() {
        if (!machine) return;
        releaseAll();
        send({ type: 'game.close', payload: {} });
        machine = null;
        state = null;
        cancelAnimationFrame(raf);
        raf = 0;
        $('game-modal').hidden = true;
    }

    /**
     * The office connection came back: re-open the machine so the server knows we are still here
     * (it holds our seat for a few seconds) and sends the current lobby or match.
     */
    function rejoin() {
        if (!machine) return;
        send({ type: 'game.open', payload: { machineId: machine.id, game: machine.game } });
    }

    /** True while the game window is open: the office ignores keys so your avatar stays put. */
    function capturesKeys() {
        return !!machine;
    }

    function receive(msg) {
        if (!machine || msg?.payload?.machineId !== machine.id) return;
        if (msg.type === 'game.state') {
            state = msg.payload;
            meta = state.meta || meta;
            if (state.status !== 'playing') { frame = prev = null; releaseAll(); quiz.myPick = null; quiz.key = ''; board.key = ''; board.sel = -1; board.promo = null; }
            renderLobby();
        } else if (msg.type === 'game.frame') {
            prev = frame;
            prevAt = frameAt;
            frame = msg.payload.s;
            frameAt = performance.now();
            if (FIELDS[machine.game] === null) renderDom();
            else if (!raf) raf = requestAnimationFrame(draw);
        } else if (msg.type === 'game.error') {
            quiz.error = t('games.quiz.start_failed');
            renderLobby();
        }
    }

    // ── lobby ──

    function seated() {
        return !!state?.players?.some((p) => p.id === me.id);
    }

    function button(label, variant, onClick, disabled) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = `ula-btn ula-btn--${variant} ula-btn--md`;
        b.textContent = label;
        b.disabled = !!disabled;
        b.addEventListener('click', onClick);
        return b;
    }

    function renderLobby() {
        const playing = state?.status === 'playing';
        const isDom = !!machine && FIELDS[machine.game] === null;
        $('game-stage').hidden = !playing;
        $('game-lobby').hidden = playing && seated();
        $('game-canvas').hidden = isDom;
        $('game-dom').hidden = !isDom;
        if (playing && isDom) renderDom();
        else if (playing) fitCanvas();

        const seats = $('game-seats');
        seats.innerHTML = '';
        const max = state?.max || 0;
        // Big tables (quiz: 10) list the people, then one line for the free seats.
        const compact = max > 6;
        const freeSeats = max - (state?.players?.length || 0);
        for (let i = 0; i < max; i++) {
            const p = state.players.find((x) => x.slot === i);
            if (compact && !p) continue;
            const li = document.createElement('li');
            li.className = 'game-seat' + (p ? '' : ' is-empty');
            const dot = document.createElement('span');
            dot.className = 'game-seat__dot';
            if (p) dot.style.background = colorOf(p.slot);
            const name = document.createElement('span');
            name.textContent = p ? p.name + (p.id === me.id ? ` (${t('games.you')})` : '') + (p.id === state.hostId ? ' ★' : '') : t('games.empty_seat');
            li.append(dot, name);
            seats.appendChild(li);
        }

        if (compact && freeSeats > 0) {
            const li = document.createElement('li');
            li.className = 'game-seat is-empty';
            li.textContent = t('games.free_seats', { n: freeSeats });
            seats.appendChild(li);
        }

        const watching = (state?.watching || []).map((w) => w.name);
        $('game-watching').textContent = watching.length ? `${t('games.watching')}: ${watching.join('، ')}` : '';

        const actions = $('game-actions');
        actions.innerHTML = '';
        const note = $('game-note');
        note.textContent = '';
        if (!state) return;

        if (playing) {
            note.textContent = t('games.playing_now');
        } else if (seated()) {
            const host = state.hostId === me.id;
            const enough = state.players.length >= state.min;
            if (host && machine.game === 'quiz') {
                actions.appendChild(quizPicker());
                actions.appendChild(button(t('games.start'), 'primary', startQuiz, !enough || !quiz.pick));
            } else if (host) {
                actions.appendChild(button(t('games.start'), 'primary', () => send({ type: 'game.start', payload: {} }), !enough));
            }
            actions.appendChild(button(t('games.stand'), 'outline', () => send({ type: 'game.stand', payload: {} })));
            if (!enough) note.textContent = t('games.need_players', { n: state.min });
            else if (!host) note.textContent = t('games.waiting_host', { name: state.players[0].name });
            if (quiz.error) note.textContent = quiz.error;
        } else if (state.players.length < state.max) {
            actions.appendChild(button(t('games.sit'), 'primary', () => send({ type: 'game.sit', payload: {} })));
        } else {
            note.textContent = t('games.full');
        }

        const res = state.lastResults;
        const box = $('game-results');
        box.hidden = !res || !res.length;
        const body = $('game-results-body');
        body.innerHTML = '';
        for (const r of res || []) {
            const tr = document.createElement('tr');
            for (const v of [r.place, r.name, r.score]) {
                const td = document.createElement('td');
                td.textContent = String(v);
                tr.appendChild(td);
            }
            if (r.id === me.id) tr.className = 'is-me';
            body.appendChild(tr);
        }
    }

    // ── input ──

    const KEYMAP = {
        snipers: { KeyW: 'up', ArrowUp: 'up', Space: 'up', KeyS: 'a', ArrowDown: 'a', Enter: 'a', KeyF: 'a' },
        racing: { KeyW: 'up', ArrowUp: 'up', KeyS: 'down', ArrowDown: 'down', KeyA: 'left', ArrowLeft: 'left', KeyD: 'right', ArrowRight: 'right', Space: 'a', ShiftLeft: 'a', Enter: 'a' },
        karts: { KeyW: 'up', ArrowUp: 'up', KeyS: 'down', ArrowDown: 'down', KeyA: 'left', ArrowLeft: 'left', KeyD: 'right', ArrowRight: 'right', Space: 'a', ShiftLeft: 'a', Enter: 'a' },
        pool: { KeyA: 'left', ArrowLeft: 'left', KeyD: 'right', ArrowRight: 'right', Space: 'a' },
        run: { KeyW: 'up', ArrowUp: 'up', KeyS: 'down', ArrowDown: 'down', KeyA: 'left', ArrowLeft: 'left', KeyD: 'right', ArrowRight: 'right' },
    };

    function onKey(e) {
        if (!machine) return;
        if (e.key === 'Escape') return; // the window's own handler closes it
        // The window is open: nothing reaches the office (no walking, no shortcuts).
        // Default actions still happen, so Enter/Space still press the lobby buttons.
        e.stopPropagation();
        if (machine.game === 'quiz') {
            const n = /^(?:Digit|Numpad)([1-4])$/.exec(e.code);
            if (n && e.type === 'keydown' && !e.repeat) { e.preventDefault(); answer(Number(n[1]) - 1); }
            return;
        }
        if (FIELDS[machine.game] === null) return; // board games: buttons and Tab/Enter
        const action = (KEYMAP[machine.game] || {})[e.code];
        if (!action || state?.status !== 'playing' || !seated()) return;
        e.preventDefault();
        const down = e.type === 'keydown';
        if (machine.game === 'pool') {
            if (action === 'a' && down && !e.repeat) startCharge();
            if (action === 'a' && !down && aim.charging) shoot();
            held[action] = down;
            return;
        }
        setKey(action, down);
    }

    function setKey(k, v) {
        if (!machine || state?.status !== 'playing' || !seated()) return;
        held[k] = v;
        const s = JSON.stringify(held);
        if (s === lastSent) return;
        lastSent = s;
        send({ type: 'game.input', payload: { input: { ...held } } });
    }

    function releaseAll() {
        for (const k in held) held[k] = false;
        aim.charging = false;
        if (machine && lastSent !== '' && lastSent !== JSON.stringify(held)) {
            lastSent = JSON.stringify(held);
            send({ type: 'game.input', payload: { input: { ...held } } });
        }
    }

    function myPoolTurn() {
        return machine?.game === 'pool' && frame && frame.turn === me.id && frame.phase === 'aim';
    }

    function startCharge() {
        if (!myPoolTurn()) return;
        aim.charging = true;
        aim.chargeStart = performance.now();
    }

    function power() {
        // Rises and falls every 1.4 s, so letting go at the right moment matters.
        const x = ((performance.now() - aim.chargeStart) / 1400) % 1;
        return 0.08 + 0.92 * (x < 0.5 ? x * 2 : 2 - x * 2);
    }

    function shoot() {
        if (!aim.charging) return;
        aim.charging = false;
        if (!myPoolTurn()) return;
        send({ type: 'game.action', payload: { data: { angle: aim.angle, power: power() } } });
    }

    // ── drawing ──

    let scale = 1;

    function fitCanvas() {
        const cv = $('game-canvas');
        const f = FIELDS[machine.game];
        const wrap = $('game-stage');
        const maxW = Math.min(wrap.clientWidth || 960, 1100);
        const maxH = window.innerHeight * 0.68;
        scale = Math.min(maxW / f.w, maxH / f.h);
        const dpr = window.devicePixelRatio || 1;
        cv.style.width = `${Math.round(f.w * scale)}px`;
        cv.style.height = `${Math.round(f.h * scale)}px`;
        cv.width = Math.round(f.w * scale * dpr);
        cv.height = Math.round(f.h * scale * dpr);
    }

    function toField(e) {
        const r = $('game-canvas').getBoundingClientRect();
        return { x: (e.clientX - r.left) / scale, y: (e.clientY - r.top) / scale };
    }

    /** Blend positions between the last two frames so 30 fps looks smooth at 60. */
    function lerpList(list, prevList, alpha) {
        if (!prevList) return list;
        return list.map((o) => {
            const p = prevList.find((q) => q.id === o.id);
            if (!p) return o;
            const out = { ...o, x: p.x + (o.x - p.x) * alpha, y: p.y + (o.y - p.y) * alpha };
            if (typeof o.a === 'number' && typeof p.a === 'number') {
                let d = o.a - p.a;
                while (d > Math.PI) d -= Math.PI * 2;
                while (d < -Math.PI) d += Math.PI * 2;
                out.a = p.a + d * alpha;
            }
            return out;
        });
    }

    function draw() {
        raf = 0;
        if (!machine || !frame || state?.status !== 'playing') return;
        const cv = $('game-canvas');
        const ctx = cv.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        ctx.setTransform(scale * dpr, 0, 0, scale * dpr, 0, 0);
        const span = Math.max(1, frameAt - prevAt);
        const alpha = Math.min(1, (performance.now() - frameAt) / span + 0.0);
        ({ snipers: drawSnipers, racing: drawRacing, karts: drawKarts, pool: drawPool, run: drawRun })[machine.game](ctx, alpha);
        raf = requestAnimationFrame(draw);
    }

    function label(ctx, text, x, y, color, size = 13, align = 'center') {
        ctx.save();
        ctx.font = `700 ${size}px Cairo, "IBM Plex Sans Arabic", system-ui, sans-serif`;
        ctx.textAlign = align;
        ctx.textBaseline = 'middle';
        ctx.direction = rtl ? 'rtl' : 'ltr';
        ctx.lineWidth = 3;
        ctx.strokeStyle = 'rgba(0,0,0,.55)';
        ctx.strokeText(text, x, y);
        ctx.fillStyle = color;
        ctx.fillText(text, x, y);
        ctx.restore();
    }

    function banner(ctx, f, text) {
        ctx.save();
        ctx.fillStyle = 'rgba(14,28,23,.55)';
        ctx.fillRect(0, f.h / 2 - 42, f.w, 84);
        ctx.restore();
        label(ctx, text, f.w / 2, f.h / 2, ART.white, 34);
    }

    // على السطح
    function drawSnipers(ctx, alpha) {
        const f = FIELDS.snipers;
        const g = ctx.createLinearGradient(0, 0, 0, f.h);
        g.addColorStop(0, ART.sky1);
        g.addColorStop(1, ART.sky2);
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, f.w, f.h);
        for (const [x1, x2] of [[40, 400], [560, 920]]) {
            ctx.fillStyle = ART.building;
            ctx.fillRect(x1, 380, x2 - x1, f.h - 380);
            ctx.fillStyle = ART.roof;
            ctx.fillRect(x1 - 6, 376, x2 - x1 + 12, 8);
            ctx.fillStyle = ART.window;
            for (let wx = x1 + 24; wx < x2 - 20; wx += 46) for (let wy = 410; wy < f.h; wy += 44) ctx.fillRect(wx, wy, 16, 20);
        }

        const players = lerpList(frame.p, prev?.p, alpha);
        for (const p of players) {
            if (!p.alive && p.y > 600) continue;
            const c = colorOf(p.slot);
            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.fillStyle = c;
            ctx.strokeStyle = ART.platform;
            ctx.lineWidth = 2;
            for (const [rx, ry, rw, rh] of [[-12, -46, 24, 34], [-10, -12, 8, 12], [2, -12, 8, 12]]) { // body, legs
                ctx.strokeRect(rx, ry, rw, rh);
                ctx.fillRect(rx, ry, rw, rh);
            }
            ctx.beginPath();
            ctx.arc(0, -52, 9, 0, Math.PI * 2);
            ctx.fillStyle = ART.platform;
            ctx.fill();
            // Rifle at the swaying angle.
            ctx.translate(p.dir * 8, -38);
            ctx.rotate(-p.aim * p.dir);
            ctx.fillStyle = ART.ink;
            ctx.fillRect(p.dir > 0 ? 0 : -34, -2.5, 34, 5);
            ctx.restore();
            label(ctx, p.name, p.x, p.y - 74, ART.white, 12);
        }
        for (const s of frame.shots) {
            ctx.strokeStyle = s.hit ? ART.box : 'rgba(249,246,239,.7)';
            ctx.lineWidth = s.hit ? 3 : 2;
            ctx.beginPath();
            ctx.moveTo(s.x1, s.y1);
            ctx.lineTo(s.x2, s.y2);
            ctx.stroke();
        }

        // Score line and timer
        const score = frame.p.map((p) => `${p.name} ${p.wins}`).join('   ·   ');
        label(ctx, score, f.w / 2, 26, ART.white, 16);
        label(ctx, `${t('games.round')} ${frame.round}  ·  ${frame.timeLeft}`, f.w / 2, 50, ART.window, 13);
        if (frame.pause) {
            const w = frame.p.find((p) => p.id === frame.lastWinner);
            banner(ctx, f, frame.lastWinner === null ? `${t('games.round')} ${frame.round}` : w ? t('games.round_won', { name: w.name }) : t('games.draw'));
        }
    }

    // سباق
    function drawRacing(ctx, alpha) {
        const f = FIELDS.racing;
        ctx.fillStyle = ART.grass;
        ctx.fillRect(0, 0, f.w, f.h);
        ctx.fillStyle = ART.grass2;
        for (let x = 0; x < f.w; x += 40) for (let y = (x / 40) % 2 ? 0 : 20; y < f.h; y += 40) ctx.fillRect(x, y, 20, 20);

        if (meta?.track) {
            const tr = meta.track;
            const path = new Path2D();
            tr.forEach(([x, y], i) => (i ? path.lineTo(x, y) : path.moveTo(x, y)));
            path.closePath();
            ctx.lineJoin = 'round';
            ctx.lineWidth = meta.half * 2 + 10;
            ctx.setLineDash([16, 16]);
            ctx.strokeStyle = ART.kerbA;
            ctx.stroke(path);
            ctx.lineDashOffset = 16;
            ctx.strokeStyle = ART.kerbB;
            ctx.stroke(path);
            ctx.setLineDash([]);
            ctx.lineWidth = meta.half * 2;
            ctx.strokeStyle = ART.asphalt;
            ctx.stroke(path);
            ctx.lineWidth = 2;
            ctx.setLineDash([14, 18]);
            ctx.strokeStyle = ART.line;
            ctx.stroke(path);
            ctx.setLineDash([]);

            // Chequered start/finish line across the road at the first point.
            const [x0, y0] = tr[0];
            const [x1, y1] = tr[1];
            const a = Math.atan2(y1 - y0, x1 - x0);
            ctx.save();
            ctx.translate(x0, y0);
            ctx.rotate(a);
            const sq = 8;
            for (let i = -meta.half; i < meta.half; i += sq) {
                for (let j = 0; j < 2; j++) {
                    ctx.fillStyle = ((i / sq + j) & 1) ? ART.ink : ART.white;
                    ctx.fillRect(j * sq - sq, i, sq, sq);
                }
            }
            ctx.restore();
        }

        const cars = lerpList(frame.c, prev?.c, alpha);
        for (const c of cars) {
            if (c.gone) continue;
            ctx.save();
            ctx.translate(c.x, c.y);
            ctx.rotate(c.a);
            if (c.nitro) {
                ctx.fillStyle = ART.box;
                ctx.beginPath();
                ctx.moveTo(-14, -5); ctx.lineTo(-30, 0); ctx.lineTo(-14, 5);
                ctx.fill();
            }
            ctx.fillStyle = colorOf(c.slot);
            ctx.strokeStyle = ART.white;
            ctx.lineWidth = 2;
            ctx.strokeRect(-14, -8, 28, 16);
            ctx.fillRect(-14, -8, 28, 16);
            ctx.fillStyle = ART.ink;
            ctx.fillRect(2, -6, 7, 12); // windscreen
            ctx.fillRect(-12, -10, 7, 3); ctx.fillRect(-12, 7, 7, 3); ctx.fillRect(6, -10, 7, 3); ctx.fillRect(6, 7, 7, 3);
            ctx.restore();
            label(ctx, c.name, c.x, c.y - 20, ART.white, 11);
        }

        const mine = frame.c.find((c) => c.id === me.id);
        const left = rtl ? f.w - 14 : 14;
        const align = rtl ? 'right' : 'left';
        if (mine) {
            label(ctx, `${t('games.lap')} ${Math.max(1, mine.lap)}/${frame.laps}   ·   ${mine.pos}/${frame.c.length}`, left, 22, ART.white, 16, align);
            label(ctx, `${t('games.nitro')}: ${mine.nitroReady ? '●' : '○'}`, left, 46, ART.window, 13, align);
        }
        label(ctx, `${frame.time.toFixed(1)}s`, rtl ? 14 : f.w - 14, 22, ART.white, 16, rtl ? 'left' : 'right');
        if (frame.countdown) banner(ctx, f, String(frame.countdown));
        else if (frame.time < 1) banner(ctx, f, t('games.go'));
    }

    // سيارات التصادم
    function drawKarts(ctx, alpha) {
        const f = FIELDS.karts;
        ctx.fillStyle = ART.void;
        ctx.fillRect(0, 0, f.w, f.h);
        ctx.beginPath();
        ctx.arc(450, 300, frame.r, 0, Math.PI * 2);
        ctx.fillStyle = ART.platform;
        ctx.fill();
        ctx.lineWidth = 8;
        ctx.strokeStyle = ART.platformEdge;
        ctx.stroke();
        ctx.lineWidth = 1;
        ctx.strokeStyle = 'rgba(14,28,23,.12)';
        for (let rr = 60; rr < frame.r; rr += 60) { ctx.beginPath(); ctx.arc(450, 300, rr, 0, Math.PI * 2); ctx.stroke(); }

        for (const b of frame.boxes) {
            ctx.fillStyle = ART.box;
            ctx.fillRect(b.x - 11, b.y - 11, 22, 22);
            label(ctx, '⚡', b.x, b.y + 1, ART.ink, 13);
        }

        const karts = lerpList(frame.k, prev?.k, alpha);
        for (const k of karts) {
            if (k.lives <= 0 || k.out) continue;
            const c = colorOf(k.slot);
            ctx.save();
            ctx.translate(k.x, k.y);
            if (k.shield) {
                ctx.beginPath();
                ctx.arc(0, 0, 24, 0, Math.PI * 2);
                ctx.strokeStyle = 'rgba(249,246,239,.8)';
                ctx.lineWidth = 3;
                ctx.setLineDash([4, 4]);
                ctx.stroke();
                ctx.setLineDash([]);
            }
            ctx.rotate(k.a);
            if (k.boost) {
                ctx.fillStyle = ART.box;
                ctx.beginPath();
                ctx.moveTo(-16, -7); ctx.lineTo(-34, 0); ctx.lineTo(-16, 7);
                ctx.fill();
            }
            ctx.beginPath();
            ctx.arc(0, 0, 17, 0, Math.PI * 2);
            ctx.fillStyle = ART.white;
            ctx.fill();
            ctx.beginPath();
            ctx.arc(0, 0, 13, 0, Math.PI * 2);
            ctx.fillStyle = c;
            ctx.fill();
            ctx.fillStyle = ART.white;
            ctx.fillRect(4, -3, 10, 6); // nose
            ctx.restore();
            label(ctx, k.name + (k.hasBoost ? ' ⚡' : ''), k.x, k.y - 28, ART.white, 11);
        }

        // Side list: lives and knock-outs
        const x = rtl ? f.w - 14 : 14;
        const align = rtl ? 'right' : 'left';
        frame.k.forEach((k, i) => {
            const hearts = '♥'.repeat(Math.max(0, k.lives)) + '♡'.repeat(Math.max(0, 3 - k.lives));
            const y = 22 + i * 20;
            ctx.beginPath();
            ctx.arc(rtl ? x - 5 : x + 5, y, 5, 0, Math.PI * 2);
            ctx.fillStyle = colorOf(k.slot);
            ctx.fill();
            label(ctx, `${k.name}  ${hearts}  ${k.kos} KO`, rtl ? x - 16 : x + 16, y, k.lives > 0 ? ART.white : ART.roof, 13, align);
        });
        label(ctx, String(frame.timeLeft), rtl ? 14 : f.w - 14, 22, ART.white, 16, rtl ? 'left' : 'right');
        if (frame.countdown) banner(ctx, f, String(frame.countdown));
    }

    // اهرب!
    const TILE = 40;
    const DIR_ANGLE = { right: 0, down: Math.PI / 2, left: Math.PI, up: -Math.PI / 2 };

    function drawGhost(ctx, x, y, color) {
        const r = 15;
        ctx.save();
        ctx.translate(x, y);
        ctx.beginPath();
        ctx.arc(0, -2, r, Math.PI, 0);
        ctx.lineTo(r, r - 2);
        for (let i = 0; i < 4; i++) ctx.lineTo(r - (i * 2 + 1) * (r / 4), i % 2 ? r - 2 : r - 7);
        ctx.lineTo(-r, r - 2);
        ctx.closePath();
        ctx.fillStyle = color;
        ctx.fill();
        ctx.strokeStyle = ART.white;
        ctx.lineWidth = 2;
        ctx.stroke();
        for (const ex of [-5, 5]) {
            ctx.beginPath();
            ctx.arc(ex, -4, 4, 0, Math.PI * 2);
            ctx.fillStyle = ART.white;
            ctx.fill();
            ctx.beginPath();
            ctx.arc(ex + 1, -4, 2, 0, Math.PI * 2);
            ctx.fillStyle = ART.ink;
            ctx.fill();
        }
        ctx.restore();
    }

    function drawRun(ctx, alpha) {
        const f = FIELDS.run;
        ctx.fillStyle = ART.void;
        ctx.fillRect(0, 0, f.w, f.h);
        const walls = meta?.walls || [];
        ctx.fillStyle = ART.grass;
        ctx.strokeStyle = ART.platformEdge;
        ctx.lineWidth = 2;
        walls.forEach((row, y) => {
            for (let x = 0; x < row.length; x++) {
                if (row[x] !== '#') continue;
                ctx.fillRect(x * TILE + 2, y * TILE + 2, TILE - 4, TILE - 4);
                ctx.strokeRect(x * TILE + 2, y * TILE + 2, TILE - 4, TILE - 4);
            }
        });
        const w = meta?.w || 21;
        ctx.fillStyle = ART.box;
        for (let k = 0; k < frame.coins.length; k++) {
            if (frame.coins[k] !== '1') continue;
            ctx.beginPath();
            ctx.arc((k % w) * TILE + TILE / 2, Math.floor(k / w) * TILE + TILE / 2, 4, 0, Math.PI * 2);
            ctx.fill();
        }

        const movers = lerpList(frame.m, prev?.m, alpha);
        // Runners first, ghosts on top.
        for (const m of [...movers].sort((a, b) => Number(a.ghost) - Number(b.ghost))) {
            const x = m.x * TILE + TILE / 2;
            const y = m.y * TILE + TILE / 2;
            if (m.ghost) {
                drawGhost(ctx, x, y, m.ai ? ART.roof : colorOf(m.slot));
                if (!m.ai) label(ctx, m.name, x, y - 24, ART.white, 11);
                continue;
            }
            // A chomping circle facing where it goes.
            const open = 0.25 + 0.2 * Math.abs(Math.sin(performance.now() / 90));
            const a = DIR_ANGLE[m.dir] ?? 0;
            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.arc(x, y, 15, a + open, a + Math.PI * 2 - open);
            ctx.closePath();
            ctx.fillStyle = colorOf(m.slot);
            ctx.fill();
            ctx.strokeStyle = ART.white;
            ctx.lineWidth = 2;
            ctx.stroke();
            label(ctx, m.name + (m.id === me.id ? ` (${t('games.you')})` : ''), x, y - 24, ART.white, 11);
        }

        const mine = frame.m.find((m) => m.id === me.id);
        const left = rtl ? f.w - 12 : 12;
        const align = rtl ? 'right' : 'left';
        if (mine) {
            label(ctx, `${mine.ghost ? '👻 ' + t('games.run.you_ghost') : '🏃 ' + t('games.run.you_run')} · ${mine.score}`, left, 14, ART.white, 13, align);
        }
        label(ctx, `${t('games.run.runners_left', { n: frame.runners })} · ${frame.timeLeft}`, rtl ? 12 : f.w - 12, 14, ART.window, 13, rtl ? 'left' : 'right');
        if (frame.countdown) banner(ctx, f, String(frame.countdown));
    }

    // بلياردو
    const CLOTH = { x1: 60, y1: 60, x2: 940, y2: 500 };
    const POCKETS = [[60, 60], [500, 56], [940, 60], [60, 500], [500, 504], [940, 500]];

    function drawBall(ctx, n, x, y) {
        const r = 12;
        const stripe = n >= 9;
        // 1–7 solids and 9–15 stripes share colours (1 & 9 yellow, 2 & 10 blue, …); 8 is black.
        const base = ART.ball[n > 8 ? n - 8 : n];
        ctx.save();
        ctx.beginPath();
        ctx.arc(x, y, r, 0, Math.PI * 2);
        ctx.fillStyle = stripe ? ART.white : base;
        ctx.fill();
        if (stripe) {
            ctx.clip();
            ctx.fillStyle = base;
            ctx.fillRect(x - r, y - 5, r * 2, 10);
        }
        ctx.restore();
        if (n !== 0) {
            ctx.beginPath();
            ctx.arc(x, y, 5, 0, Math.PI * 2);
            ctx.fillStyle = ART.white;
            ctx.fill();
            ctx.fillStyle = ART.ink;
            ctx.font = '700 7px system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(String(n), x, y + 0.5);
        }
        ctx.beginPath();
        ctx.arc(x, y, 12, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(0,0,0,.35)';
        ctx.lineWidth = 1;
        ctx.stroke();
    }

    function drawPool(ctx, alpha) {
        const f = FIELDS.pool;
        ctx.fillStyle = ART.ink;
        ctx.fillRect(0, 0, f.w, f.h);
        ctx.fillStyle = ART.rail;
        ctx.fillRect(30, 30, f.w - 60, f.h - 60);
        ctx.fillStyle = ART.cloth;
        ctx.fillRect(CLOTH.x1, CLOTH.y1, CLOTH.x2 - CLOTH.x1, CLOTH.y2 - CLOTH.y1);
        for (const [x, y] of POCKETS) {
            ctx.beginPath();
            ctx.arc(x, y, 22, 0, Math.PI * 2);
            ctx.fillStyle = ART.pocket;
            ctx.fill();
        }

        const balls = lerpList(frame.b.map(([n, x, y]) => ({ id: n, x, y })), prev?.b?.map(([n, x, y]) => ({ id: n, x, y })), alpha);
        for (const b of balls) drawBall(ctx, b.id, b.x, b.y);

        const cue = balls.find((b) => b.id === 0);
        if (cue && myPoolTurn()) {
            if (aim.mouse && !aim.charging) aim.angle = Math.atan2(aim.mouse.y - cue.y, aim.mouse.x - cue.x);
            if (held.left) aim.angle -= 0.02;
            if (held.right) aim.angle += 0.02;
            const pw = aim.charging ? power() : 0;
            ctx.setLineDash([6, 8]);
            ctx.strokeStyle = 'rgba(249,246,239,.6)';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(cue.x, cue.y);
            ctx.lineTo(cue.x + Math.cos(aim.angle) * 600, cue.y + Math.sin(aim.angle) * 600);
            ctx.stroke();
            ctx.setLineDash([]);
            // The cue stick pulls back as power builds.
            const back = 18 + pw * 60;
            ctx.strokeStyle = ART.window;
            ctx.lineWidth = 6;
            ctx.lineCap = 'round';
            ctx.beginPath();
            ctx.moveTo(cue.x - Math.cos(aim.angle) * back, cue.y - Math.sin(aim.angle) * back);
            ctx.lineTo(cue.x - Math.cos(aim.angle) * (back + 260), cue.y - Math.sin(aim.angle) * (back + 260));
            ctx.stroke();
            ctx.lineCap = 'butt';
            if (aim.charging) {
                ctx.fillStyle = 'rgba(14,28,23,.7)';
                ctx.fillRect(f.w / 2 - 102, f.h - 24, 204, 14);
                ctx.fillStyle = ART.box;
                ctx.fillRect(f.w / 2 - 100, f.h - 22, 200 * pw, 10);
            }
        }

        // Players, their group, whose turn
        frame.players.forEach((p, i) => {
            const g = frame.groups[p.id];
            const turn = frame.turn === p.id;
            const txt = `${turn ? '▶ ' : ''}${p.name}${g ? ' · ' + t('games.' + g) : ''}`;
            label(ctx, txt, i === 0 ? (rtl ? f.w - 40 : 40) : (rtl ? 40 : f.w - 40), 15, turn ? ART.window : ART.white, 14, (i === 0) !== rtl ? 'left' : 'right');
        });
        const who = frame.players.find((p) => p.id === frame.turn);
        const status = frame.turn === me.id ? t('games.your_turn') : t('games.their_turn', { name: who ? who.name : '' });
        label(ctx, frame.phase === 'aim' ? `${status} · ${frame.clock}` : status, f.w / 2, f.h - 16 - (aim.charging ? 18 : 0), ART.white, 13);
        if (frame.message === 'foul') label(ctx, t('games.foul'), f.w / 2, 15, ART.box, 14);
        if (frame.message === 'timeout') label(ctx, t('games.timeout'), f.w / 2, 15, ART.box, 14);
    }

    // ── quiz (سؤال وجواب) ──

    const quiz = { list: null, pick: null, error: '', myPick: null, key: '', qi: -1 };
    // Answer colours are tokens from four different families (the palette has no more), each with its
    // own text colour for contrast and its own shape, so colour is never the only cue.
    const ANSWER_STYLE = [
        ['--ula-status-danger', '--ula-text-on-accent', '▲'],
        ['--ula-surface-dark', '--ula-text-on-dark', '◆'],
        ['--ula-tone-gold-dot', '--ula-text-on-gold', '●'],
        ['--ula-status-success', '--ula-text-on-accent', '■'],
    ];

    async function loadQuizzes() {
        try {
            const res = await fetch('/games/quizzes', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            quiz.list = data.quizzes || [];
            quiz.pick = quiz.list[0]?.id || null;
        } catch (e) {
            quiz.list = [];
        }
        renderLobby();
    }

    function quizPicker() {
        const wrap = document.createElement('label');
        wrap.className = 'game-quiz-picker';
        const span = document.createElement('span');
        span.textContent = t('games.quiz.pick');
        const sel = document.createElement('select');
        for (const q of quiz.list || []) {
            const o = document.createElement('option');
            o.value = q.id;
            o.textContent = `${q.title} · ${q.count}`;
            o.selected = q.id === quiz.pick;
            sel.appendChild(o);
        }
        sel.addEventListener('change', () => { quiz.pick = sel.value; quiz.error = ''; renderLobby(); });
        wrap.append(span, sel);
        return wrap;
    }

    async function startQuiz() {
        if (!quiz.pick) return;
        quiz.error = '';
        try {
            const res = await fetch(`/games/quizzes/${encodeURIComponent(quiz.pick)}/ticket`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            if (!res.ok) throw new Error(String(res.status));
            const data = await res.json();
            send({ type: 'game.start', payload: { ticket: data.ticket } });
        } catch (e) {
            quiz.error = t('games.quiz.start_failed');
            renderLobby();
        }
    }

    function answer(i) {
        if (!frame || frame.phase !== 'question' || !seated() || quiz.myPick !== null || i >= frame.c.length) return;
        quiz.myPick = i;
        send({ type: 'game.action', payload: { data: { choice: i } } });
        renderQuiz();
    }

    function el(tag, cls, text) {
        const n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined) n.textContent = text;
        return n;
    }

    function renderQuiz() {
        const box = $('game-dom');
        const f = frame;
        if (!f) { box.textContent = ''; return; }
        if (f.phase === 'question' && quiz.qi !== f.i) { quiz.qi = f.i; quiz.myPick = null; }
        const key = [f.phase, f.i, f.left, f.answered.length, quiz.myPick].join('|');
        if (key === quiz.key) return;
        quiz.key = key;
        box.textContent = '';

        const head = el('div', 'game-quiz__head');
        head.append(
            el('span', 'game-quiz__count', t('games.quiz.progress', { i: f.i + 1, n: f.n })),
            el('span', 'game-quiz__title', f.title),
        );
        if (f.phase === 'question') {
            const timer = el('span', 'game-quiz__timer', String(f.left));
            timer.setAttribute('aria-label', t('games.quiz.seconds_left', { s: f.left }));
            head.appendChild(timer);
        }
        box.appendChild(head);

        if (f.phase === 'intro') {
            box.append(el('p', 'game-quiz__ready', t('games.quiz.get_ready')), el('p', 'game-quiz__big', String(f.left)));
            return;
        }

        box.appendChild(el('h3', 'game-quiz__question', f.q));

        const grid = el('div', 'game-quiz__answers');
        const reveal = f.phase === 'reveal';
        const canAnswer = f.phase === 'question' && seated() && quiz.myPick === null;
        f.c.forEach((text, i) => {
            const [color, ink, shape] = ANSWER_STYLE[i];
            const b = el('button', 'game-quiz__answer');
            b.type = 'button';
            b.style.background = `var(${color})`;
            b.style.color = `var(${ink})`;
            b.disabled = !canAnswer;
            if (quiz.myPick === i) b.classList.add('is-mine');
            if (reveal) b.classList.add(i === f.correct ? 'is-correct' : 'is-wrong');
            b.append(el('span', 'game-quiz__shape', shape), el('span', 'game-quiz__text', text));
            if (reveal && f.picks) {
                const pickers = Object.values(f.picks).filter((v) => v === i).length;
                b.appendChild(el('span', 'game-quiz__picks', String(pickers)));
            }
            b.setAttribute('aria-label', `${i + 1}. ${text}`);
            b.addEventListener('click', () => answer(i));
            grid.appendChild(b);
        });
        box.appendChild(grid);

        const status = el('p', 'game-quiz__status');
        status.setAttribute('role', 'status');
        if (f.phase === 'question') {
            status.textContent = quiz.myPick !== null
                ? t('games.quiz.answer_sent')
                : seated() ? t('games.quiz.answered', { a: f.answered.length, n: f.scores.length }) : t('games.playing_now');
        } else if (reveal && seated()) {
            const got = (f.gained && f.gained[me.id]) || 0;
            status.textContent = got > 0 ? t('games.quiz.right', { p: got }) : t('games.quiz.wrong');
            status.classList.add(got > 0 ? 'is-right' : 'is-wrong');
        }
        box.appendChild(status);

        if (reveal) {
            const board = el('ol', 'game-quiz__board');
            f.scores.slice(0, 5).forEach((s) => {
                const li = el('li', s.id === me.id ? 'is-me' : '');
                const dot = el('span', 'game-seat__dot');
                dot.style.background = colorOf(s.slot);
                const gained = (f.gained && f.gained[s.id]) || 0;
                li.append(dot, el('span', 'game-quiz__name', s.name));
                if (gained) li.appendChild(el('span', 'game-quiz__gained', `+${gained}`));
                li.appendChild(el('span', 'game-quiz__score', String(s.score)));
                board.appendChild(li);
            });
            box.appendChild(board);
        }
    }

    // ── board games: شطرنج and إكس أو ──

    const board = { sel: -1, promo: null, key: '' };
    // ︎ asks for the text form: the pawn otherwise turns into an emoji that ignores our colours.
    const GLYPH = { k: '♚︎', q: '♛︎', r: '♜︎', b: '♝︎', n: '♞︎', p: '♟︎' };
    const PIECE_NAME = { k: 'king', q: 'queen', r: 'rook', b: 'bishop', n: 'knight', p: 'pawn' };
    const sqName = (s) => 'abcdefgh'[s % 8] + (Math.floor(s / 8) + 1);
    const clockText = (s) => `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;

    function chessAct(data) {
        send({ type: 'game.action', payload: { data } });
    }

    function renderChess(box) {
        const f = frame;
        const myColor = f.white.id === me.id ? 'w' : f.black.id === me.id ? 'b' : null;
        const flip = myColor === 'b';
        const myTurn = myColor === f.turn && !f.outcome;
        const key = [f.board, f.turn, f.clocks.w, f.clocks.b, board.sel, board.promo?.to, f.drawOffer, f.outcome?.reason].join('|');
        if (key === board.key) return;
        board.key = key;
        if (!myTurn) { board.sel = -1; board.promo = null; }
        box.textContent = '';

        const wrap = el('div', 'game-chess');
        const side = (c) => {
            const p = c === 'w' ? f.white : f.black;
            const row = el('div', 'game-chess__player' + (f.turn === c && !f.outcome ? ' is-turn' : ''));
            const dot = el('span', 'game-seat__dot');
            dot.style.background = colorOf(p.slot);
            row.append(dot, el('span', 'game-chess__name', `${c === 'w' ? '♔' : '♚'} ${p.name}${p.id === me.id ? ` (${t('games.you')})` : ''}`), el('span', 'game-chess__clock', clockText(f.clocks[c])));
            return row;
        };
        const top = side(flip ? 'w' : 'b');
        const bottom = side(flip ? 'b' : 'w');

        const grid = el('div', 'game-chess__board');
        grid.setAttribute('role', 'grid');
        grid.setAttribute('aria-label', t('games.name.chess.ar'));
        const targets = new Map((board.sel >= 0 ? f.legal.filter((m) => m[0] === board.sel) : []).map((m) => [m[1], m[2]]));
        const mine = (pc) => myColor && pc !== '.' && ((pc === pc.toUpperCase()) === (myColor === 'w'));
        for (let row = 0; row < 8; row++) {
            for (let col = 0; col < 8; col++) {
                const r = flip ? row : 7 - row;
                const c = flip ? 7 - col : col;
                const s = r * 8 + c;
                const pc = f.board[s];
                const b = el('button', 'game-chess__sq ' + ((r + c) % 2 ? 'is-light' : 'is-dark'));
                b.type = 'button';
                if (f.last && (f.last[0] === s || f.last[1] === s)) b.classList.add('is-last');
                if (f.check === s) b.classList.add('is-check');
                if (board.sel === s) b.classList.add('is-selected');
                if (targets.has(s)) b.classList.add(pc === '.' ? 'is-target' : 'is-capture');
                if (pc !== '.') {
                    const g = el('span', 'game-chess__piece ' + (pc === pc.toUpperCase() ? 'is-white' : 'is-black'), GLYPH[pc.toLowerCase()]);
                    b.appendChild(g);
                }
                b.setAttribute('aria-label', `${sqName(s)}${pc !== '.' ? ' ' + (pc === pc.toUpperCase() ? 'white' : 'black') + ' ' + PIECE_NAME[pc.toLowerCase()] : ''}`);
                b.disabled = !myTurn;
                b.addEventListener('click', () => {
                    if (targets.has(s)) {
                        if (targets.get(s)) { board.promo = { from: board.sel, to: s }; board.key = ''; renderDom(); return; }
                        chessAct({ from: board.sel, to: s });
                        board.sel = -1;
                    } else {
                        board.sel = mine(pc) && board.sel !== s ? s : -1;
                    }
                    board.key = '';
                    renderDom();
                });
                grid.appendChild(b);
            }
        }

        const panel = el('div', 'game-chess__panel');
        const status = el('p', 'game-chess__status');
        status.setAttribute('role', 'status');
        if (f.outcome) {
            const winner = f.outcome.winner === 'w' ? f.white : f.outcome.winner === 'b' ? f.black : null;
            status.textContent = (winner ? t('games.chess.wins', { name: winner.name }) : t('games.chess.draw')) + ' — ' + t('games.chess.reason.' + f.outcome.reason);
        } else {
            status.textContent = myTurn ? t('games.your_turn') : t('games.their_turn', { name: (f.turn === 'w' ? f.white : f.black).name });
        }
        panel.appendChild(status);

        if (board.promo) {
            const pick = el('div', 'game-chess__promo');
            pick.appendChild(el('span', '', t('games.chess.promote')));
            for (const p of ['q', 'r', 'b', 'n']) {
                const pb = el('button', 'ula-btn ula-btn--outline ula-btn--md', GLYPH[p]);
                pb.type = 'button';
                pb.setAttribute('aria-label', PIECE_NAME[p]);
                pb.addEventListener('click', () => { chessAct({ ...board.promo, promo: p }); board.promo = null; board.sel = -1; board.key = ''; });
                pick.appendChild(pb);
            }
            panel.appendChild(pick);
        }

        if (myColor && !f.outcome) {
            const acts = el('div', 'game-chess__actions');
            const them = myColor === 'w' ? 'b' : 'w';
            if (f.drawOffer === them) {
                panel.appendChild(el('p', 'game-chess__offer', t('games.chess.offered')));
                acts.append(button(t('games.chess.accept'), 'primary', () => chessAct({ draw: 'accept' })), button(t('games.chess.decline'), 'outline', () => chessAct({ draw: 'decline' })));
            } else if (f.drawOffer === myColor) {
                panel.appendChild(el('p', 'game-chess__offer', t('games.chess.offer_sent')));
            } else {
                acts.appendChild(button(t('games.chess.offer'), 'outline', () => chessAct({ draw: 'offer' })));
            }
            acts.appendChild(button(t('games.chess.resign'), 'danger', () => { if (confirm(t('games.chess.resign_confirm'))) chessAct({ resign: true }); }));
            panel.appendChild(acts);
        }

        const col = el('div', 'game-chess__col');
        col.append(top, grid, bottom);
        wrap.append(col, panel);
        box.appendChild(wrap);
    }

    function renderTicTacToe(box) {
        const f = frame;
        const meP = f.players.find((p) => p.id === me.id);
        const myTurn = !!meP && f.turn === me.id && !f.pause && !f.over;
        const key = [f.cells, f.turn, f.clock, f.round, f.pause, f.over].join('|');
        if (key === board.key) return;
        board.key = key;
        box.textContent = '';

        const head = el('div', 'game-ttt__score');
        f.players.forEach((p) => {
            const s = el('div', 'game-ttt__player' + (f.turn === p.id && !f.pause ? ' is-turn' : ''));
            const dot = el('span', 'game-seat__dot');
            dot.style.background = colorOf(p.slot);
            s.append(dot, el('span', 'game-ttt__mark', p.mark), el('span', '', p.name + (p.id === me.id ? ` (${t('games.you')})` : '')), el('span', 'game-ttt__wins', String(p.wins)));
            head.appendChild(s);
        });
        box.appendChild(head);
        box.appendChild(el('p', 'game-ttt__round', t('games.ttt.round', { r: f.round, n: f.need })));

        const cells = f.cells.split(',');
        const grid = el('div', 'game-ttt__grid');
        cells.forEach((v, i) => {
            const b = el('button', 'game-ttt__cell' + (f.line && f.line.includes(i) ? ' is-line' : ''), v);
            b.type = 'button';
            b.disabled = !myTurn || !!v;
            b.setAttribute('aria-label', `${i + 1}${v ? ' ' + v : ''}`);
            b.addEventListener('click', () => chessAct({ cell: i }));
            grid.appendChild(b);
        });
        box.appendChild(grid);

        const status = el('p', 'game-ttt__status');
        status.setAttribute('role', 'status');
        if (f.pause) {
            const w = f.players.find((p) => p.id === f.roundWinner);
            status.textContent = f.roundWinner === 'draw' ? t('games.draw') : t('games.round_won', { name: w ? w.name : '' });
        } else {
            const who = f.players.find((p) => p.id === f.turn);
            status.textContent = `${myTurn ? t('games.your_turn') : t('games.their_turn', { name: who ? who.name : '' })} · ${f.clock}`;
        }
        box.appendChild(status);
    }

    /** HTML-drawn games (quiz, chess, tic-tac-toe) share one stage. */
    function renderDom() {
        const box = $('game-dom');
        if (!frame) { box.textContent = ''; return; }
        if (machine.game === 'quiz') return renderQuiz();
        if (machine.game === 'chess') return renderChess(box);
        if (machine.game === 'tictactoe') return renderTicTacToe(box);
    }

    window.UlaGames = { init, open, receive, capturesKeys, rejoin };
})();

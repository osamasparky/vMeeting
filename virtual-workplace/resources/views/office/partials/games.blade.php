{{-- Office arcade window: lobby (seats, watchers, last results) and the play canvas.
     Driven by public/js/office/games.js; the realtime server referees the match. --}}
<style>
    .game-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--ula-space-4);
        background: var(--ula-surface-overlay);
    }
    .game-modal[hidden] { display: none; }
    .game-card {
        width: min(1140px, 100%);
        max-height: calc(100vh - 32px);
        overflow-y: auto;
        background: var(--ula-surface-card);
        color: var(--ula-text-primary);
        border: 1px solid var(--ula-border-subtle);
        border-radius: var(--ula-radius-xl);
        box-shadow: var(--ula-shadow-lg);
        padding: var(--ula-space-5);
    }
    .game-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: var(--ula-space-3);
        margin-block-end: var(--ula-space-4);
    }
    .game-card__close {
        min-width: 44px;
        min-height: 44px;
        border-radius: var(--ula-radius-md);
        border: 1px solid var(--ula-border-subtle);
        background: var(--ula-surface-raised);
        color: var(--ula-text-primary);
        cursor: pointer;
    }
    .game-card__close:focus-visible,
    .game-modal .ula-btn:focus-visible { outline: none; box-shadow: var(--ula-focus-ring); }
    .game-lobby { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: var(--ula-space-5); }
    .game-lobby[hidden], .game-stage[hidden], .game-results[hidden] { display: none; }
    .game-section-title { margin: 0 0 var(--ula-space-2); font-size: var(--ula-size-sm); font-weight: 800; color: var(--ula-text-secondary); }
    .game-seats { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
    .game-seat {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        padding-inline: 12px;
        border-radius: var(--ula-radius-md);
        background: var(--ula-surface-raised);
        border: 1px solid var(--ula-border-subtle);
        font-weight: 700;
    }
    .game-seat.is-empty { color: var(--ula-text-muted); border-style: dashed; font-weight: 500; }
    .game-seat__dot { width: 14px; height: 14px; border-radius: var(--ula-radius-pill); background: var(--ula-border-subtle); flex: none; }
    .game-watching, .game-note { margin: var(--ula-space-2) 0 0; font-size: var(--ula-size-xs); color: var(--ula-text-secondary); }
    .game-actions { display: flex; flex-wrap: wrap; gap: var(--ula-space-2); margin-block-start: var(--ula-space-3); }
    .game-help { margin: 0; font-size: var(--ula-size-sm); line-height: 1.7; color: var(--ula-text-secondary); }
    .game-results table { width: 100%; border-collapse: collapse; font-size: var(--ula-size-sm); }
    .game-results td { padding: 8px; border-block-end: 1px solid var(--ula-border-subtle); }
    .game-results td:first-child, .game-results td:last-child { font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; }
    .game-results tr.is-me td { font-weight: 800; color: var(--ula-accent-default); }
    .game-stage { display: flex; flex-direction: column; align-items: center; gap: var(--ula-space-3); }
    .game-stage canvas { display: block; max-width: 100%; border-radius: var(--ula-radius-lg); background: var(--ula-surface-dark); }
</style>

<div id="game-modal" class="game-modal" role="dialog" aria-modal="true" aria-labelledby="game-title-ar" hidden>
    <div class="game-card">
        <div class="game-card__head">
            <div class="ula-headline-group">
                <h2 id="game-title-ar" class="ula-headline-ar" style="margin: 0;"></h2>
                <p id="game-title-en" class="ula-headline-en" style="margin: 0;"></p>
            </div>
            <button type="button" id="game-close" class="game-card__close" aria-label="{{ __('games.close') }}">
                <span class="material-symbols-rounded" aria-hidden="true">close</span>
            </button>
        </div>

        <div id="game-lobby" class="game-lobby">
            <section>
                <h3 class="game-section-title">{{ __('games.players') }}</h3>
                <ul id="game-seats" class="game-seats"></ul>
                <p id="game-watching" class="game-watching"></p>
                <div id="game-actions" class="game-actions"></div>
                <p id="game-note" class="game-note" role="status"></p>
            </section>
            <section>
                <h3 class="game-section-title">{{ __('games.controls') }}</h3>
                <p id="game-help" class="game-help"></p>
                <div id="game-results" class="game-results" hidden>
                    <h3 class="game-section-title" style="margin-block-start: var(--ula-space-4);">{{ __('games.last_results') }}</h3>
                    <table>
                        <thead><tr><th scope="col" style="text-align: start;">#</th><th scope="col" style="text-align: start;">{{ __('games.player') }}</th><th scope="col" style="text-align: start;">{{ __('games.score') }}</th></tr></thead>
                        <tbody id="game-results-body"></tbody>
                    </table>
                </div>
            </section>
        </div>

        <div id="game-stage" class="game-stage" hidden>
            <canvas id="game-canvas" aria-label="{{ __('games.canvas_label') }}"></canvas>
        </div>
    </div>
</div>

@php
    $gameText = collect([
        'games.you', 'games.empty_seat', 'games.watching', 'games.start', 'games.stand', 'games.sit', 'games.need_players',
        'games.waiting_host', 'games.full', 'games.playing_now', 'games.round', 'games.round_won', 'games.draw', 'games.lap',
        'games.nitro', 'games.go', 'games.your_turn', 'games.their_turn', 'games.foul', 'games.timeout', 'games.solids', 'games.stripes',
        'games.help.snipers', 'games.help.racing', 'games.help.karts', 'games.help.pool',
    ])->mapWithKeys(fn ($k) => [$k => __($k)])->all();
    // Titles pair the Arabic name with its English companion whatever the interface language.
    foreach (['snipers' => ['على السطح', 'Rooftop'], 'racing' => ['سباق', 'Racing'], 'karts' => ['سيارات التصادم', 'Bumper Karts'], 'pool' => ['بلياردو', '8-Ball Pool']] as $g => [$ar, $en]) {
        $gameText["games.name.$g.ar"] = $ar;
        $gameText["games.name.$g.en"] = $en;
    }
@endphp
<script nonce="{{ $cspNonce ?? '' }}">
    window.UlaGamesText = @json($gameText);
</script>

{{-- The personal assistant, on every member page (dashboard, project hub, office, map editor):
     a floating avatar in the bottom inline-end corner, a speech bubble that shows one unread
     notification at a time (with previous / next) or the one thing that matters now, and a
     panel with the person's summary and a box to ask for things.
     Behaviour: public/js/assistant.js. Data: AssistantController + the existing /api/notifications.
     A page that has something in that corner sets --ula-assistant-offset (see the office). --}}
@auth
@if(empty(auth()->user()->is_guest))
<style>
    .ula-assistant {
        position: fixed;
        inset-block-end: var(--ula-assistant-offset, var(--ula-space-6));
        inset-inline-end: var(--ula-space-6);
        z-index: 900;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: var(--ula-space-3);
        pointer-events: none;
    }
    .ula-assistant > * { pointer-events: auto; }
    /* The dashboard's and the project hub's toasts use this corner too: they stack above the avatar. */
    #toast-container, #hub-toast-container { bottom: calc(var(--ula-assistant-offset, var(--ula-space-6)) + 84px); }

    .ula-assistant__avatar {
        position: relative;
        width: 64px;
        height: 64px;
        padding: 0;
        border-radius: var(--ula-radius-pill);
        border: 3px solid var(--ula-highlight-default);
        background: var(--ula-surface-card);
        box-shadow: var(--ula-shadow-lg);
        cursor: pointer;
        transition: transform var(--ula-duration-fast) var(--ula-ease-out);
    }
    .ula-assistant__avatar:hover { transform: translateY(-2px); }
    .ula-assistant__avatar:focus-visible, .ula-assistant button:focus-visible, .ula-assistant input:focus-visible {
        outline: none;
        box-shadow: var(--ula-focus-ring);
    }
    .ula-assistant__avatar img { width: 100%; height: 100%; border-radius: var(--ula-radius-pill); object-fit: cover; display: block; }
    .ula-assistant__badge {
        position: absolute;
        inset-block-start: -4px;
        inset-inline-end: -4px;
        min-width: 22px;
        height: 22px;
        padding-inline: 6px;
        border-radius: var(--ula-radius-pill);
        background: var(--ula-status-danger);
        color: var(--ula-text-on-accent);
        font: 700 12px/22px var(--ula-font-mono);
        text-align: center;
        direction: ltr;
        unicode-bidi: isolate;
        box-shadow: 0 0 0 2px var(--ula-surface-card);
    }
    .ula-assistant__badge[hidden] { display: none; }

    .ula-assistant__bubble {
        position: relative;
        max-width: min(320px, calc(100vw - 48px));
        padding: var(--ula-space-3) var(--ula-space-4);
        border-radius: var(--ula-radius-lg);
        background: var(--ula-surface-card);
        color: var(--ula-text-primary);
        border: 1px solid var(--ula-border-subtle);
        box-shadow: var(--ula-shadow-lg);
        animation: ula-assistant-in var(--ula-duration-slow) var(--ula-ease-entrance);
    }
    .ula-assistant__bubble[hidden] { display: none; }
    .ula-assistant__bubble::after {
        content: '';
        position: absolute;
        inset-block-end: -7px;
        inset-inline-end: 26px;
        width: 12px;
        height: 12px;
        background: var(--ula-surface-card);
        border-inline-end: 1px solid var(--ula-border-subtle);
        border-block-end: 1px solid var(--ula-border-subtle);
        transform: rotate(45deg);
    }
    .ula-assistant__bubble { width: min(340px, calc(100vw - 48px)); }
    .ula-assistant__bubble-head { display: flex; align-items: center; gap: var(--ula-space-2); margin-block-end: 4px; font-size: var(--ula-size-xs); font-weight: 800; color: var(--ula-text-secondary); }
    .ula-assistant__bubble-head .material-symbols-rounded { font-size: 18px; color: var(--ula-icon-highlight); }
    .ula-assistant__bubble-kicker { flex: 1; min-width: 0; }
    /* "1 من 3" / "1 of 3": a sentence in the page's language, kept apart from its neighbours. */
    .ula-assistant__bubble-counter { unicode-bidi: isolate; white-space: nowrap; }
    .ula-assistant__bubble-title { margin: 0; font-size: var(--ula-size-sm); font-weight: 800; line-height: 1.6; overflow-wrap: anywhere; }
    .ula-assistant__bubble-sub { margin: 2px 0 0; font-size: var(--ula-size-xs); color: var(--ula-text-secondary); line-height: 1.6; overflow-wrap: anywhere; }
    .ula-assistant__bubble-sub:empty { display: none; }
    .ula-assistant__bubble-actions { display: flex; flex-wrap: wrap; align-items: center; gap: var(--ula-space-2); margin-block-start: var(--ula-space-2); }
    .ula-assistant__bubble-actions .ula-assistant__spacer { flex: 1; }
    .ula-assistant__bubble-actions [hidden] { display: none; }
    .ula-assistant__icon-btn:disabled { opacity: .35; cursor: default; }
    .ula-assistant__icon-btn .material-symbols-rounded { font-size: 20px; }
    /* Arrows point the way the pages turn: mirrored in RTL. */
    [dir="rtl"] .ula-assistant__flip { transform: scaleX(-1); }
    .ula-assistant__alerts-cta { display: flex; align-items: center; gap: var(--ula-space-2); padding: var(--ula-space-2) var(--ula-space-3); border-radius: var(--ula-radius-md); background: var(--ula-surface-gold-soft); }
    .ula-assistant__alerts-cta p { flex: 1; margin: 0; font-size: var(--ula-size-sm); font-weight: 800; }
    .ula-assistant__icon-btn {
        min-width: 44px;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: var(--ula-radius-md);
        background: transparent;
        color: var(--ula-text-secondary);
        cursor: pointer;
    }
    .ula-assistant__icon-btn:hover { background: var(--ula-surface-hover); color: var(--ula-text-primary); }

    .ula-assistant__panel {
        width: min(400px, calc(100vw - 32px));
        max-height: min(620px, calc(100vh - 160px));
        display: flex;
        flex-direction: column;
        border-radius: var(--ula-radius-xl);
        background: var(--ula-surface-card);
        color: var(--ula-text-primary);
        border: 1px solid var(--ula-border-subtle);
        box-shadow: var(--ula-shadow-lg);
        overflow: hidden;
        animation: ula-assistant-in var(--ula-duration-slow) var(--ula-ease-entrance);
    }
    .ula-assistant__panel[hidden] { display: none; }
    /* Phones: a bottom sheet. */
    @media (max-width: 640px) {
        .ula-assistant__panel {
            position: fixed;
            inset-inline: 0;
            inset-block-end: 0;
            width: auto;
            max-height: 78vh;
            border-end-start-radius: 0;
            border-end-end-radius: 0;
        }
        /* The sheet has its own close button; the avatar would sit on top of it. */
        .ula-assistant.is-open .ula-assistant__avatar { display: none; }
    }
    .ula-assistant__head {
        display: flex;
        align-items: center;
        gap: var(--ula-space-3);
        padding: var(--ula-space-3) var(--ula-space-4);
        border-block-end: 1px solid var(--ula-border-subtle);
    }
    .ula-assistant__head img { width: 40px; height: 40px; border-radius: var(--ula-radius-pill); object-fit: cover; border: 2px solid var(--ula-highlight-default); }
    .ula-assistant__head .ula-headline-group { flex: 1; min-width: 0; }
    .ula-assistant__head .ula-headline-ar { margin: 0; font-size: var(--ula-size-body); }
    .ula-assistant__head .ula-headline-en { margin: 0; font-size: var(--ula-size-xs); }
    .ula-assistant__tabs { display: flex; gap: 4px; padding: var(--ula-space-2) var(--ula-space-3) 0; }
    .ula-assistant__tab {
        flex: 1;
        min-height: 44px;
        border: 0;
        border-block-end: 3px solid transparent;
        background: transparent;
        color: var(--ula-text-secondary);
        font-weight: 800;
        cursor: pointer;
    }
    .ula-assistant__tab[aria-selected="true"] { color: var(--ula-accent-default); border-block-end-color: var(--ula-accent-default); }
    .ula-assistant__tab .ula-assistant__count:not(:empty) {
        display: inline-block;
        margin-inline-start: 6px;
        min-width: 20px;
        padding-inline: 6px;
        border-radius: var(--ula-radius-pill);
        background: var(--ula-surface-gold-soft);
        color: var(--ula-text-primary);
        font: 700 11px/20px var(--ula-font-mono);
        direction: ltr;
        unicode-bidi: isolate;
    }
    .ula-assistant__body { flex: 1; overflow-y: auto; padding: var(--ula-space-3) var(--ula-space-4) var(--ula-space-4); display: flex; flex-direction: column; gap: var(--ula-space-3); }
    .ula-assistant__notice { margin: 0; padding: var(--ula-space-2) var(--ula-space-3); border-radius: var(--ula-radius-md); background: var(--ula-surface-gold-soft); color: var(--ula-text-primary); font-size: var(--ula-size-xs); font-weight: 700; }
    .ula-assistant__notice[hidden] { display: none; }
    .ula-assistant__greeting { margin: 0; font-size: var(--ula-size-body); font-weight: 800; }
    .ula-assistant__headline { margin: 0; color: var(--ula-text-secondary); font-size: var(--ula-size-sm); line-height: 1.6; }
    .ula-assistant__section h3 { margin: 0 0 6px; font-size: var(--ula-size-xs); font-weight: 800; color: var(--ula-text-secondary); text-transform: none; }
    .ula-assistant__list { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
    .ula-assistant__item {
        display: flex;
        align-items: center;
        gap: var(--ula-space-2);
        padding: var(--ula-space-2) var(--ula-space-3);
        border-radius: var(--ula-radius-md);
        background: var(--ula-surface-raised);
        border: 1px solid var(--ula-border-subtle);
    }
    .ula-assistant__item.is-unread { border-inline-start: 3px solid var(--ula-highlight-default); }
    .ula-assistant__item .material-symbols-rounded { color: var(--ula-icon-accent); flex: none; }
    .ula-assistant__item-text { flex: 1; min-width: 0; }
    .ula-assistant__item-title { margin: 0; font-size: var(--ula-size-sm); font-weight: 800; overflow-wrap: anywhere; }
    .ula-assistant__item-sub { margin: 2px 0 0; font-size: var(--ula-size-xs); color: var(--ula-text-secondary); overflow-wrap: anywhere; }
    /* Day + time, a date or "Live now": isolated in its own direction so numbers never flip. */
    .ula-assistant__meta { unicode-bidi: plaintext; }
    .ula-assistant__tag-late { color: var(--ula-status-danger); font-weight: 800; }
    .ula-assistant__tag-live { color: var(--ula-status-success); font-weight: 800; }
    .ula-assistant__item-actions { display: flex; gap: 4px; flex: none; }
    .ula-assistant__filters { display: flex; flex-wrap: wrap; gap: 6px; }
    .ula-assistant__chip {
        min-height: 36px;
        padding-inline: 12px;
        border-radius: var(--ula-radius-pill);
        border: 1px solid var(--ula-border-subtle);
        background: var(--ula-surface-raised);
        color: var(--ula-text-primary);
        font-size: var(--ula-size-xs);
        font-weight: 800;
        cursor: pointer;
    }
    .ula-assistant__chip[aria-pressed="true"] { background: var(--ula-accent-default); border-color: var(--ula-accent-default); color: var(--ula-text-on-accent); }
    .ula-assistant__empty { margin: var(--ula-space-4) 0; text-align: center; color: var(--ula-text-muted); font-size: var(--ula-size-sm); }
    .ula-assistant__log { display: flex; flex-direction: column; gap: var(--ula-space-2); }
    .ula-assistant__msg { max-width: 90%; padding: var(--ula-space-2) var(--ula-space-3); border-radius: var(--ula-radius-lg); font-size: var(--ula-size-sm); line-height: 1.6; }
    .ula-assistant__msg.is-me { align-self: flex-end; background: var(--ula-accent-default); color: var(--ula-text-on-accent); }
    .ula-assistant__msg.is-bot { align-self: flex-start; background: var(--ula-surface-raised); border: 1px solid var(--ula-border-subtle); }
    .ula-assistant__ask { display: flex; gap: var(--ula-space-2); padding: var(--ula-space-3) var(--ula-space-4); border-block-start: 1px solid var(--ula-border-subtle); }
    .ula-assistant__ask[hidden] { display: none; }
    .ula-assistant__ask input {
        flex: 1;
        min-width: 0;
        min-height: 44px;
        padding-inline: 12px;
        border-radius: var(--ula-radius-md);
        border: 1px solid var(--ula-border-subtle);
        background: var(--ula-surface-raised);
        color: var(--ula-text-primary);
        font-size: var(--ula-size-sm);
    }
    .ula-assistant__note { margin: 0; font-size: var(--ula-size-xs); color: var(--ula-text-muted); }
    .ula-assistant__vh { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    @keyframes ula-assistant-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) {
        .ula-assistant__bubble, .ula-assistant__panel { animation: none; }
        .ula-assistant__avatar { transition: none; }
        .ula-assistant__avatar:hover { transform: none; }
    }
</style>

<div id="ula-assistant" class="ula-assistant">
    <div id="ula-assistant-bubble" class="ula-assistant__bubble" role="region" aria-label="{{ __('assistant.bubble_label') }}" hidden>
        <div class="ula-assistant__bubble-head">
            <span class="material-symbols-rounded" id="ula-assistant-bubble-icon" aria-hidden="true">notifications</span>
            <span class="ula-assistant__bubble-kicker" id="ula-assistant-bubble-kicker"></span>
            <span class="ula-assistant__bubble-counter" id="ula-assistant-bubble-counter"></span>
        </div>
        <div aria-live="polite">
            <p class="ula-assistant__bubble-title" id="ula-assistant-bubble-text"></p>
            <p class="ula-assistant__bubble-sub" id="ula-assistant-bubble-sub"></p>
        </div>
        <div class="ula-assistant__bubble-actions">
            <button type="button" id="ula-assistant-bubble-action" class="ula-btn ula-btn--primary ula-btn--sm" hidden></button>
            <button type="button" id="ula-assistant-bubble-read" class="ula-btn ula-btn--ghost ula-btn--sm" hidden>{{ __('assistant.mark_read') }}</button>
            <span class="ula-assistant__spacer"></span>
            <button type="button" id="ula-assistant-bubble-prev" class="ula-assistant__icon-btn" aria-label="{{ __('assistant.previous') }}" hidden>
                <span class="material-symbols-rounded ula-assistant__flip" aria-hidden="true">chevron_left</span>
            </button>
            <button type="button" id="ula-assistant-bubble-next" class="ula-assistant__icon-btn" aria-label="{{ __('assistant.next') }}" hidden>
                <span class="material-symbols-rounded ula-assistant__flip" aria-hidden="true">chevron_right</span>
            </button>
            <button type="button" id="ula-assistant-bubble-close" class="ula-assistant__icon-btn" aria-label="{{ __('assistant.dismiss') }}">
                <span class="material-symbols-rounded" aria-hidden="true">close</span>
            </button>
        </div>
    </div>

    <section id="ula-assistant-panel" class="ula-assistant__panel" role="dialog" aria-modal="false" aria-labelledby="ula-assistant-title" hidden>
        <header class="ula-assistant__head">
            <img src="/images/assistant/ula-assistant-avatar.webp" alt="" width="40" height="40">
            <div class="ula-headline-group">
                <h2 id="ula-assistant-title" class="ula-headline-ar">{{ __('assistant.name') }}</h2>
                @if(app()->getLocale() === 'ar')<p class="ula-headline-en" lang="en" dir="ltr">Your assistant</p>@endif
            </div>
            <button type="button" id="ula-assistant-alerts" class="ula-assistant__icon-btn" aria-label="{{ __('assistant.show_alerts') }}" title="{{ __('assistant.show_alerts') }}">
                <span class="material-symbols-rounded" aria-hidden="true">notifications</span>
            </button>
            <button type="button" id="ula-assistant-close" class="ula-assistant__icon-btn" aria-label="{{ __('assistant.close') }}">
                <span class="material-symbols-rounded" aria-hidden="true">close</span>
            </button>
        </header>
        <div class="ula-assistant__tabs" role="tablist" aria-label="{{ __('assistant.name') }}">
            <button type="button" role="tab" class="ula-assistant__tab" id="ula-assistant-tab-summary" data-tab="summary" aria-controls="ula-assistant-body" aria-selected="true">{{ __('assistant.tab.summary') }}</button>
            <button type="button" role="tab" class="ula-assistant__tab" id="ula-assistant-tab-ask" data-tab="ask" aria-controls="ula-assistant-body" aria-selected="false" tabindex="-1">{{ __('assistant.tab.ask') }}</button>
        </div>
        <p id="ula-assistant-offline" class="ula-assistant__notice" role="status" hidden>{{ __('assistant.offline') }}</p>
        <div id="ula-assistant-body" class="ula-assistant__body" role="tabpanel" aria-live="polite"></div>
        <form id="ula-assistant-ask" class="ula-assistant__ask" hidden>
            <label for="ula-assistant-input" class="ula-assistant__vh">{{ __('assistant.tab.ask') }}</label>
            <input id="ula-assistant-input" type="text" maxlength="300" autocomplete="off" placeholder="{{ __('assistant.ask_placeholder') }}">
            <button type="submit" class="ula-btn ula-btn--primary ula-btn--md" aria-label="{{ __('assistant.send') }}">
                <span class="material-symbols-rounded ula-btn__icon" aria-hidden="true">send</span>
            </button>
        </form>
    </section>

    <button type="button" id="ula-assistant-toggle" class="ula-assistant__avatar" aria-expanded="false" aria-controls="ula-assistant-panel" aria-label="{{ __('assistant.open') }}">
        <img src="/images/assistant/ula-assistant-avatar.webp" alt="" width="64" height="64">
        <span id="ula-assistant-badge" class="ula-assistant__badge" hidden></span>
    </button>
</div>

@php
    $assistantText = collect([
        'name', 'close', 'dismiss', 'section.meetings', 'section.invitations', 'section.tasks', 'section.messages', 'section.alerts',
        'filter.all', 'filter.unread', 'filter.task', 'filter.meeting', 'filter.office', 'mark_read', 'mark_all', 'empty_alerts',
        'loading', 'error', 'retry', 'rules_note', 'unread_badge', 'room_elsewhere', 'overdue', 'live_now', 'answer.all_clear',
        'action.open', 'action.view', 'open', 'show_alerts', 'alert_kind.task', 'alert_kind.meeting', 'alert_kind.office',
        'alert_kind.other', 'no_more_alerts', 'no_alerts_filtered', 'counter', 'unread_cta', 'previous', 'next',
    ])->mapWithKeys(fn ($k) => [$k => __('assistant.'.$k)])->all();
@endphp
<script nonce="{{ $cspNonce ?? '' }}">
    window.UlaAssistantText = @json($assistantText);
    // The same office notification types the bell files under "office".
    window.UlaAssistantOfficeTypes = @json(\App\Support\NotificationPreferences::OFFICE_TYPES);
</script>
<script src="/js/assistant.js?v={{ filemtime(public_path('js/assistant.js')) }}" nonce="{{ $cspNonce ?? '' }}"></script>
@endif
@endauth

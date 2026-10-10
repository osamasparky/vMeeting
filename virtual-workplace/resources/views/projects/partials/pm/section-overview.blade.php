{{-- Overview tab: project health (latest status update), progress by stage,
     what needs attention, upcoming milestones and the updates feed. --}}
@php
    $ovToday = now()->startOfDay();
    $ovWeekEnd = now()->endOfWeek();
    $ovOpen = $tasks->where('status', '!=', 'done');
    $ovOverdue = $ovOpen->filter(fn ($t) => $t->due_date && $t->due_date->lt($ovToday))->sortBy('due_date');
    $ovDueWeek = $ovOpen->filter(fn ($t) => $t->due_date && $t->due_date->between($ovToday, $ovWeekEnd));
    $ovUnassigned = $ovOpen->filter(fn ($t) => ! $t->assignee_id);
    $ovDoneWeek = $tasks->filter(fn ($t) => $t->status === 'done' && $t->completed_at && $t->completed_at->gte(now()->startOfWeek()));
    $ovStages = $project->states->map(fn ($s) => [
        'name' => $s->displayName(),
        'count' => $tasks->filter(fn ($t) => $t->state_id === $s->id)->count(),
    ]);
    $ovMax = max(1, $ovStages->max('count'));
    $ovMilestones = $project->milestones->where('status', '!=', 'completed')->sortBy('due_date')->take(3);
    $ovLatest = $project->updates()->with('user:id,name')->latest()->first();
    $ovHealthTone = ['on_track' => 'palm', 'at_risk' => 'gold', 'off_track' => 'terracotta'];
    $ovHealthIcon = ['on_track' => 'rocket_launch', 'at_risk' => 'warning', 'off_track' => 'error'];
@endphp
<div id="hub-section-overview" class="hub-section-content" style="display: none;">
    <div class="pm-ov-grid">
        {{-- Health + updates --}}
        <section class="pm-panel pm-ov-wide" aria-labelledby="pm-ov-health-title">
            <div class="pm-panel-head">
                <div class="ula-headline-group">
                    <h3 class="ula-headline-ar" id="pm-ov-health-title">{{ __('pm.project_health') }}</h3>
                    @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Project health</span>@endif
                </div>
                @if($ovLatest)
                    <span class="pm-label pm-label--{{ $ovHealthTone[$ovLatest->health] }}" style="font-size: var(--ula-size-sm); padding-block: var(--ula-space-2); gap: var(--ula-space-2);">
                        <span class="material-symbols-rounded" aria-hidden="true" style="font-size: 18px;">{{ $ovHealthIcon[$ovLatest->health] }}</span>
                        {{ __('pm.health_'.$ovLatest->health) }}
                    </span>
                @else
                    <span class="pm-label pm-label--stone">{{ __('pm.no_update_yet') }}</span>
                @endif
            </div>

            <form class="pm-ov-update-form" onsubmit="pmPostUpdate(event)">
                <div class="pm-row" role="radiogroup" aria-label="{{ __('pm.project_health') }}">
                    @foreach(['on_track', 'at_risk', 'off_track'] as $h)
                        <label class="pm-ov-health-choice">
                            <input type="radio" name="health" value="{{ $h }}" @checked($h === ($ovLatest->health ?? 'on_track'))>
                            <span class="pm-label pm-label--{{ $ovHealthTone[$h] }}" style="gap: var(--ula-space-2);">
                                <span class="material-symbols-rounded" aria-hidden="true" style="font-size: 16px;">{{ $ovHealthIcon[$h] }}</span>{{ __('pm.health_'.$h) }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <textarea class="pm-field" name="body" rows="3" maxlength="5000" required placeholder="{{ __('pm.update_placeholder') }}" aria-label="{{ __('pm.update_placeholder') }}"></textarea>
                <div><x-btn type="submit" variant="primary" size="md" icon="campaign">{{ __('pm.post_update') }}</x-btn></div>
            </form>

            <div id="pm-update-feed" class="pm-list" style="margin-block-start: var(--ula-space-5);" aria-live="polite"></div>
        </section>

        {{-- Key numbers --}}
        <section class="pm-panel" aria-labelledby="pm-ov-attention-title">
            <h3 class="pm-extras-label" id="pm-ov-attention-title" style="margin: 0 0 var(--ula-space-4);">{{ __('pm.needs_attention') }}</h3>
            <dl class="pm-ov-stats">
                <div><dt>{{ __('Overdue') }}</dt><dd class="pm-num">{{ $ovOverdue->count() }}</dd></div>
                <div><dt>{{ __('pm.due_this_week') }}</dt><dd class="pm-num">{{ $ovDueWeek->count() }}</dd></div>
                <div><dt>{{ __('Unassigned') }}</dt><dd class="pm-num">{{ $ovUnassigned->count() }}</dd></div>
                <div><dt>{{ __('pm.done_this_week') }}</dt><dd class="pm-num">{{ $ovDoneWeek->count() }}</dd></div>
            </dl>
            @if($ovOverdue->isNotEmpty())
                <div class="pm-list" style="margin-block-start: var(--ula-space-4);">
                    @foreach($ovOverdue->take(5) as $ot)
                        <button type="button" class="pm-list-row pm-sprint-row" onclick="openTaskInspector('{{ $ot->id }}')">
                            <span class="pm-num pm-muted">#{{ $ot->task_number }}</span>
                            <span class="pm-grow" style="font-weight: 700;">{{ $ot->title }}</span>
                            <span class="pm-num" style="color: var(--ula-text-danger);">{{ $ot->due_date->format('Y-m-d') }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Progress by stage --}}
        <section class="pm-panel" aria-labelledby="pm-ov-stage-title">
            <h3 class="pm-extras-label" id="pm-ov-stage-title" style="margin: 0 0 var(--ula-space-4);">{{ __('pm.tasks_by_stage') }}</h3>
            <div class="pm-ov-bars" role="list">
                @foreach($ovStages as $stage)
                    <div class="pm-ov-bar-row" role="listitem">
                        <span class="pm-ov-bar-label">{{ $stage['name'] }}</span>
                        <span class="pm-ov-bar-track"><span class="pm-ov-bar" style="inline-size: {{ round($stage['count'] / $ovMax * 100) }}%;"></span></span>
                        <span class="pm-num pm-ov-bar-value">{{ $stage['count'] }}</span>
                    </div>
                @endforeach
            </div>
            <p class="pm-muted" style="margin: var(--ula-space-4) 0 0;">
                {{ __('pm.overall_progress') }}: <span class="pm-num">{{ $kpis['progress_pct'] ?? 0 }}%</span>
            </p>
        </section>

        {{-- Upcoming milestones --}}
        <section class="pm-panel" aria-labelledby="pm-ov-ms-title">
            <h3 class="pm-extras-label" id="pm-ov-ms-title" style="margin: 0 0 var(--ula-space-4);">{{ __('pm.upcoming_milestones') }}</h3>
            @forelse($ovMilestones as $oms)
                <div class="pm-list-row" style="margin-block-end: var(--ula-space-2);">
                    <span class="material-symbols-rounded" aria-hidden="true" style="color: var(--ula-icon-accent);">flag</span>
                    <span class="pm-grow" style="font-weight: 700;">{{ $oms->name }}</span>
                    <span class="pm-muted pm-num">{{ $oms->due_date?->format('Y-m-d') ?? '—' }}</span>
                    <span class="pm-num">{{ $oms->progress_percentage }}%</span>
                </div>
            @empty
                <div class="pm-empty">{{ __('pm.no_upcoming_milestones') }}</div>
            @endforelse
        </section>
    </div>
</div>

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
    {{-- What needs attention, at a glance --}}
    <dl class="pm-ov-stats" aria-label="{{ __('pm.needs_attention') }}">
        <div class="{{ $ovOverdue->isNotEmpty() ? 'is-alert' : '' }}">
            <dt>@if($ovOverdue->isNotEmpty())<span class="material-symbols-rounded" aria-hidden="true">warning</span>@endif{{ __('Overdue') }}</dt>
            <dd class="pm-num">{{ $ovOverdue->count() }}</dd>
        </div>
        <div><dt>{{ __('pm.due_this_week') }}</dt><dd class="pm-num">{{ $ovDueWeek->count() }}</dd></div>
        <div><dt>{{ __('Unassigned') }}</dt><dd class="pm-num">{{ $ovUnassigned->count() }}</dd></div>
        <div><dt>{{ __('pm.done_this_week') }}</dt><dd class="pm-num">{{ $ovDoneWeek->count() }}</dd></div>
    </dl>

    <div class="pm-ov-layout">
        <div class="pm-ov-side">
        {{-- Health + updates --}}
        <section class="pm-panel pm-ov-main" aria-labelledby="pm-ov-health-title">
            <div class="pm-panel-head">
                <div class="ula-headline-group">
                    <h3 class="ula-headline-ar" id="pm-ov-health-title">{{ __('pm.project_health') }}</h3>
                    @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Project health</span>@endif
                </div>
                @if($ovLatest)
                    <span class="pm-label pm-label--{{ $ovHealthTone[$ovLatest->health] }} pm-ov-health-now">
                        <span class="material-symbols-rounded" aria-hidden="true">{{ $ovHealthIcon[$ovLatest->health] }}</span>
                        {{ __('pm.health_'.$ovLatest->health) }}
                    </span>
                @else
                    <span class="pm-label pm-label--stone">{{ __('pm.no_update_yet') }}</span>
                @endif
            </div>

            <form class="pm-ov-update-form" onsubmit="pmPostUpdate(event)">
                <fieldset class="pm-ov-health-set">
                    <legend class="pm-extras-label">{{ __('pm.project_health') }}</legend>
                    @foreach(['on_track', 'at_risk', 'off_track'] as $h)
                        <label class="pm-ov-health-choice pm-ov-health-choice--{{ $ovHealthTone[$h] }}">
                            <input type="radio" name="health" value="{{ $h }}" @checked($h === ($ovLatest->health ?? 'on_track'))>
                            <span class="material-symbols-rounded" aria-hidden="true">{{ $ovHealthIcon[$h] }}</span>
                            <span>{{ __('pm.health_'.$h) }}</span>
                        </label>
                    @endforeach
                </fieldset>
                <textarea class="pm-field" name="body" rows="3" maxlength="5000" required placeholder="{{ __('pm.update_placeholder') }}" aria-label="{{ __('pm.update_placeholder') }}"></textarea>
                <div><x-btn type="submit" variant="primary" size="md" icon="campaign">{{ __('pm.post_update') }}</x-btn></div>
            </form>

            <div id="pm-update-feed" class="pm-list pm-ov-feed" aria-live="polite"></div>
        </section>

        {{-- Progress by stage --}}
        <section class="pm-panel" aria-labelledby="pm-ov-stage-title">
            <div class="ula-headline-group pm-ov-card-head">
                <h3 class="ula-headline-ar" id="pm-ov-stage-title">{{ __('pm.tasks_by_stage') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Tasks by stage</span>@endif
            </div>
            <div class="pm-ov-bars" role="list">
                @foreach($ovStages as $stage)
                    <div class="pm-ov-bar-row" role="listitem">
                        <span class="pm-ov-bar-label">{{ $stage['name'] }}</span>
                        <span class="pm-ov-bar-track"><span class="pm-ov-bar" style="inline-size: {{ round($stage['count'] / $ovMax * 100) }}%;"></span></span>
                        <span class="pm-num pm-ov-bar-value">{{ $stage['count'] }}</span>
                    </div>
                @endforeach
            </div>
            <p class="pm-ov-progress">
                <span>{{ __('pm.overall_progress') }}</span>
                <span class="pm-ov-progress-track" aria-hidden="true"><span style="inline-size: {{ $kpis['progress_pct'] ?? 0 }}%;"></span></span>
                <strong class="pm-num">{{ $kpis['progress_pct'] ?? 0 }}%</strong>
            </p>
        </section>

        </div>

        <div class="pm-ov-side">
            {{-- Overdue work --}}
            @if($ovOverdue->isNotEmpty())
                <section class="pm-panel" aria-labelledby="pm-ov-overdue-title">
                    <div class="ula-headline-group pm-ov-card-head">
                        <h3 class="ula-headline-ar" id="pm-ov-overdue-title">{{ __('pm.overdue_tasks') }}</h3>
                        @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Overdue tasks</span>@endif
                    </div>
                    <div class="pm-list">
                        @foreach($ovOverdue->take(5) as $ot)
                            <button type="button" class="pm-ov-task" onclick="openTaskInspector('{{ $ot->id }}')">
                                <span class="pm-ov-task-title" dir="auto">{{ $ot->title }}</span>
                                <span class="pm-ov-task-meta">
                                    <span class="pm-num pm-muted">#{{ $ot->task_number }}</span>
                                    <span class="pm-num pm-ov-late">{{ $ot->due_date->format('Y-m-d') }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Upcoming milestones --}}
            <section class="pm-panel" aria-labelledby="pm-ov-ms-title">
                <div class="ula-headline-group pm-ov-card-head">
                    <h3 class="ula-headline-ar" id="pm-ov-ms-title">{{ __('pm.upcoming_milestones') }}</h3>
                    @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Upcoming milestones</span>@endif
                </div>
                @forelse($ovMilestones as $oms)
                    <div class="pm-ov-ms">
                        <span class="material-symbols-rounded pm-ov-ms-icon" aria-hidden="true">flag</span>
                        <div class="pm-ov-ms-body">
                            <span class="pm-ov-ms-name" dir="auto">{{ $oms->name }}</span>
                            <span class="pm-ov-ms-meta">
                                <span class="pm-num pm-muted">{{ $oms->due_date?->format('Y-m-d') ?? '—' }}</span>
                                <span class="pm-ov-progress-track" aria-hidden="true"><span style="inline-size: {{ $oms->progress_percentage }}%;"></span></span>
                                <span class="pm-num">{{ $oms->progress_percentage }}%</span>
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="pm-empty">{{ __('pm.no_upcoming_milestones') }}</div>
                @endforelse
            </section>
        </div>
    </div>
</div>

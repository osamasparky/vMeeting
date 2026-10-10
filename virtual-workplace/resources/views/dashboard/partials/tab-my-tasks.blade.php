<div id="tab-my-tasks" class="tab-view">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <h2 class="ula-headline-ar" style="font-size: var(--ula-size-h1); margin: 0;">{{ __('page.my_tasks') }}</h2>
            @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-h4);">My Tasks &amp; Action Items</span>@endif
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <x-btn variant="primary" size="md" onclick="openNewTaskModal()" icon="add">
                {{ __('New Task') }}
            </x-btn>
        </div>
    </div>

    {{-- My work: planner (every project, grouped by due date) or the status board --}}
    <div class="pm-row mw-toolbar">
        <div class="mw-segmented" role="tablist" aria-label="{{ __('pm.mywork_view') }}">
            <button type="button" role="tab" id="mw-view-planner" aria-controls="mywork-planner" aria-selected="true" onclick="mwSetView('planner')">
                <span class="material-symbols-rounded" aria-hidden="true">event_note</span>{{ __('pm.mywork_planner') }}</button>
            <button type="button" role="tab" id="mw-view-board" aria-controls="mywork-board" aria-selected="false" onclick="mwSetView('board')">
                <span class="material-symbols-rounded" aria-hidden="true">view_kanban</span>{{ __('pm.mywork_board') }}</button>
        </div>
        <div class="pm-row" id="mw-scopes" role="group" aria-label="{{ __('pm.mywork_scope') }}">
            @foreach(['assigned' => __('pm.mywork_assigned'), 'created' => __('pm.mywork_created'), 'following' => __('pm.mywork_following')] as $scopeKey => $scopeLabel)
                <button type="button" class="mw-chip" data-scope="{{ $scopeKey }}" aria-pressed="{{ $scopeKey === 'assigned' ? 'true' : 'false' }}" onclick="mwSetScope('{{ $scopeKey }}')">{{ $scopeLabel }}</button>
            @endforeach
        </div>
        <span class="pm-muted pm-grow" id="mw-summary" aria-live="polite" style="text-align: end;"></span>
    </div>

    <div id="mywork-planner" role="tabpanel" aria-labelledby="mw-view-planner">
        <div class="pm-empty">{{ __('pm.loading') }}</div>
    </div>

    <div id="mywork-board" role="tabpanel" aria-labelledby="mw-view-board" hidden>
    <!-- Task Status Columns Grid (5-Column Kanban matching All Tasks) -->
    <div class="ula-kanban-5">

        @php
            $myKanbanCols = [
                'backlog' => ['title' => __('Backlog'), 'icon' => 'inventory_2', 'color' => 'var(--ula-text-secondary)', 'border' => 'var(--ula-border-subtle)'],
                'ready' => ['title' => __('Ready'), 'icon' => 'adjust', 'color' => 'var(--ula-accent-default)', 'border' => 'var(--ula-accent-default)'],
                'in_progress' => ['title' => __('In Progress'), 'icon' => 'bolt', 'color' => 'var(--ula-accent-press)', 'border' => 'var(--ula-accent-press)'],
                'review' => ['title' => __('Review / QA'), 'icon' => 'pageview', 'color' => 'var(--ula-gold-400)', 'border' => 'var(--ula-gold-400)'],
                'done' => ['title' => __('Done'), 'icon' => 'check_circle', 'color' => 'var(--ula-status-success)', 'border' => 'var(--ula-status-success)'],
            ];
        @endphp

        @foreach($myKanbanCols as $colKey => $colMeta)
            @php
                $colTasks = ($colKey === 'review') ? $myTasks->whereIn('status', ['review', 'qa']) : $myTasks->where('status', $colKey);
            @endphp
            <div class="card mytasks-kanban-column" id="mytasks-kanban-zone-{{ $colKey }}" style="border-radius: var(--ula-radius-lg); padding: var(--ula-space-5); background: var(--ula-surface-page-alt); display: flex; flex-direction: column; border: 1px solid var(--ula-border-subtle);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid {{ $colMeta['border'] }}; padding-bottom: 10px; margin-bottom: var(--ula-space-4);">
                    <h3 style="font-size: var(--ula-size-sm); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); display: flex; align-items: center; gap: 6px; margin: 0;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: {{ $colMeta['color'] }};">{{ $colMeta['icon'] }}</span>
                        <span>{{ $colMeta['title'] }}</span>
                    </h3>
                    <span class="nav-badge-pill" id="mytasks-kanban-cnt-{{ $colKey }}" style="font-weight: var(--ula-weight-bold); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $colTasks->count() }}</span>
                </div>

                <div class="kanban-cards-container" id="mytasks-kanban-col-{{ $colKey }}" data-status="{{ $colKey }}" style="display: flex; flex-direction: column; gap: 10px; flex: 1; min-height: 120px;">
                    @forelse($colTasks as $t)
                        @php
                            $canEditThisTask = $user->can('update', $t);
                            $isManager = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || ($t->project && $t->project->manager_id === $user->id));
                        @endphp
                        <x-task-card :task="$t" context="mytasks" :can-edit="$canEditThisTask" :is-manager="$isManager" />
                    @empty
                        <div class="mytasks-empty-hint" id="mytasks-empty-{{ $colKey }}" style="text-align: center; padding: 18px 8px; color: var(--ula-text-muted); font-size: var(--ula-size-xs); border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-md);">
                            {{ __('No tasks in this stage.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
    </div>
</div>

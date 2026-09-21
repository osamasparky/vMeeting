<div id="tab-my-tasks" class="tab-view">
    <div style="display: flex; justify-content: flex-end; margin-bottom: var(--ula-space-6);">
        <x-btn variant="primary" size="md" onclick="openNewTaskModal()" icon="add">
            {{ __('New Task') }}
        </x-btn>
    </div>

    <!-- Task Status Columns Grid (5-Column Kanban matching All Tasks) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--ula-space-5);">
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
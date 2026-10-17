<?php

namespace App\Domains\Assistant;

use App\Domains\Chat\Models\Channel;
use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Notifications\Support\NotificationCategories;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Services\OfficeTimezone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * What one person should look at right now, read only from their own data:
 * their meetings and invitations, their tasks, direct messages sent to them,
 * and their notifications. Nothing here reads another member's private data,
 * and every query is scoped to the person's organization.
 *
 * Every item carries an "action" the client can perform — open a URL, walk to
 * a room, open a direct message — never a write.
 */
class AssistantBriefing
{
    public const SOON_MINUTES = 30;

    public function __construct(private readonly OfficeTimezone $timezones) {}

    public function forMember(User $user, OrganizationMember $membership): array
    {
        $tz = $this->timezones->forMember($user->id, $membership->organization_id);
        $meetings = $this->meetings($user, $membership, $tz);
        $tasks = $this->tasks($user, $membership, $tz);
        $messages = $this->messages($user, $membership);
        $notifications = $this->notifications($user);

        return [
            'greeting' => $this->greeting($user, $tz),
            'headline' => $this->headline($meetings, $tasks, $messages, $notifications),
            'meetings' => $meetings['upcoming'],
            'invitations' => $meetings['invitations'],
            'tasks' => $tasks,
            'messages' => $messages,
            'notifications' => $notifications,
            'timezone' => $tz,
        ];
    }

    /**
     * The person's own meetings (organized or invited to) that are live now or
     * start within the next day, and invitations to the coming week.
     */
    public function meetings(User $user, OrganizationMember $membership, string $tz): array
    {
        $mine = Meeting::withoutGlobalScopes()
            ->where('organization_id', $membership->organization_id)
            ->whereIn('status', ['scheduled', 'pending', 'active'])
            ->where(fn ($q) => $q->where('created_by', $user->id)->orWhereHas('participants', fn ($p) => $p->where('user_id', $user->id)))
            ->where(fn ($q) => $q->where('status', 'active')->orWhereBetween('scheduled_at', [now()->subMinutes(15), now()->addDays(7)]))
            ->with(['room:id,name', 'creator:id,name', 'project:id,name'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->limit(20)
            ->get();

        $present = fn (Meeting $m) => [
            'id' => $m->id,
            'title' => $m->title,
            'status' => $m->status,
            'starts_at' => $m->scheduled_at?->toIso8601String(),
            'time' => $m->scheduled_at?->setTimezone($tz)->format('H:i'),
            'day' => $m->scheduled_at ? $this->dayLabel($m->scheduled_at, $tz) : null,
            'minutes_until' => $m->scheduled_at ? (int) floor(now()->diffInMinutes($m->scheduled_at, false)) : null,
            'room' => $m->room?->name,
            'organizer' => $m->creator?->name,
            'project' => $m->project?->name,
            'invited' => $m->created_by !== $user->id,
            'action' => $m->room_id
                // In the office the client walks there; elsewhere it opens the office at that room.
                ? ['type' => 'room', 'room_id' => $m->room_id, 'url' => '/office?room='.$m->room_id, 'label' => __('assistant.action.join')]
                : ['type' => 'url', 'url' => '/dashboard#meetings', 'label' => __('assistant.action.view')],
        ];

        $upcoming = $mine->filter(fn ($m) => $m->status === 'active' || ($m->scheduled_at && $m->scheduled_at->lte(now()->addDay())))->values();
        $invitations = $mine->filter(fn ($m) => $m->created_by !== $user->id && $m->status !== 'active' && $m->scheduled_at?->gt(now()->addDay()))->values();

        return ['upcoming' => $upcoming->map($present)->all(), 'invitations' => $invitations->take(5)->map($present)->all()];
    }

    /** Tasks assigned to the person: overdue first, then due today. */
    public function tasks(User $user, OrganizationMember $membership, string $tz): array
    {
        $today = now()->setTimezone($tz)->toDateString();
        $open = Task::forOrganization($membership->organization_id)
            ->assignedTo($user->id)
            ->where('status', '!=', Task::STATUS_DONE)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $today)
            ->with('project:id,name')
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        $present = fn (Task $t) => [
            'id' => $t->id,
            'title' => $t->title,
            'project' => $t->project?->name,
            'due' => $t->due_date?->toDateString(),
            'overdue' => $t->due_date?->toDateString() < $today,
            'action' => ['type' => 'url', 'url' => NotificationService::taskUrl($t->project_id, $t->id), 'label' => __('assistant.action.open_task')],
        ];

        return [
            'overdue' => $open->filter(fn ($t) => $t->due_date->toDateString() < $today)->take(5)->map($present)->values()->all(),
            'today' => $open->filter(fn ($t) => $t->due_date->toDateString() === $today)->take(5)->map($present)->values()->all(),
            'overdue_count' => $open->filter(fn ($t) => $t->due_date->toDateString() < $today)->count(),
        ];
    }

    /**
     * Direct messages other people sent the person in the last day. Chat does
     * not track reading yet, so these are "recent", never called "unread".
     */
    public function messages(User $user, OrganizationMember $membership): array
    {
        return Channel::where('organization_id', $membership->organization_id)
            ->where('type', 'dm')
            ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->with(['messages' => fn ($q) => $q->where('created_at', '>=', now()->subDay())->where('sender_id', '!=', $user->id)->latest()->with('sender:id,name')])
            ->get()
            ->map(fn (Channel $c) => $c->messages->sortByDesc('created_at')->first())
            ->filter()
            ->sortByDesc('created_at')
            ->take(5)
            ->map(fn ($msg) => [
                'from' => $msg->sender?->name,
                'from_id' => $msg->sender_id,
                'preview' => Str::limit(strip_tags((string) $msg->body), 80),
                'at' => $msg->created_at?->toIso8601String(),
                'ago' => $msg->created_at?->diffForHumans(),
                'action' => ['type' => 'dm', 'user_id' => $msg->sender_id, 'url' => '/dashboard#chat', 'label' => __('assistant.action.reply')],
            ])
            ->values()
            ->all();
    }

    /** Unread counts per bell category and the latest few unread, with their own links. */
    public function notifications(User $user): array
    {
        $unread = WorkplaceNotification::forUser($user->id)->unread()->latest()->limit(50)->get(['id', 'type', 'title', 'body', 'action_url', 'created_at']);
        $counts = ['task' => 0, 'meeting' => 0, 'office' => 0, 'other' => 0];
        foreach ($unread as $n) {
            $counts[NotificationCategories::of($n->type)]++;
        }

        return [
            'unread' => WorkplaceNotification::forUser($user->id)->unread()->count(),
            'counts' => $counts,
            'mentions' => $unread->where('type', 'task_mention')->count(),
            'latest' => $unread->take(5)->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'category' => NotificationCategories::of($n->type),
                'title' => $n->title,
                'body' => Str::limit((string) $n->body, 100),
                'ago' => $n->created_at?->diffForHumans(),
                'action' => $n->action_url ? ['type' => 'url', 'url' => $n->action_url, 'label' => __('assistant.action.open')] : null,
            ])->values()->all(),
        ];
    }

    private function greeting(User $user, string $tz): string
    {
        $hour = (int) now()->setTimezone($tz)->format('G');
        $first = Str::of($user->name)->explode(' ')->first();

        return __($hour < 12 ? 'assistant.greeting.morning' : ($hour < 18 ? 'assistant.greeting.afternoon' : 'assistant.greeting.evening'), ['name' => $first]);
    }

    /** The one thing worth saying first, or null when nothing needs attention. */
    private function headline(array $meetings, array $tasks, array $messages, array $notifications): ?array
    {
        $live = collect($meetings['upcoming'])->firstWhere('status', 'active');
        if ($live) {
            return ['text' => __('assistant.headline.live', ['title' => $live['title']]), 'action' => $live['action'], 'key' => 'live-'.$live['id']];
        }
        $soon = collect($meetings['upcoming'])->first(fn ($m) => $m['minutes_until'] !== null && $m['minutes_until'] >= 0 && $m['minutes_until'] <= self::SOON_MINUTES);
        if ($soon) {
            return ['text' => __('assistant.headline.soon', ['title' => $soon['title'], 'minutes' => max(1, $soon['minutes_until'])]), 'action' => $soon['action'], 'key' => 'soon-'.$soon['id']];
        }
        if ($tasks['overdue_count'] > 0) {
            return ['text' => trans_choice('assistant.headline.overdue', $tasks['overdue_count'], ['count' => $tasks['overdue_count']]), 'action' => ['type' => 'panel', 'tab' => 'summary'], 'key' => 'overdue-'.$tasks['overdue_count']];
        }
        if ($notifications['mentions'] > 0) {
            return ['text' => trans_choice('assistant.headline.mentions', $notifications['mentions'], ['count' => $notifications['mentions']]), 'action' => ['type' => 'alerts', 'filter' => 'task', 'label' => __('assistant.show_alerts')], 'key' => 'mentions-'.$notifications['mentions']];
        }
        if ($meetings['invitations']) {
            $first = $meetings['invitations'][0];

            return ['text' => __('assistant.headline.invited', ['title' => $first['title'], 'name' => $first['organizer'] ?? '']), 'action' => ['type' => 'panel', 'tab' => 'summary'], 'key' => 'invite-'.$first['id']];
        }
        if ($messages) {
            return ['text' => __('assistant.headline.message', ['name' => $messages[0]['from']]), 'action' => $messages[0]['action'], 'key' => 'msg-'.$messages[0]['at']];
        }
        if ($notifications['unread'] > 0) {
            return ['text' => trans_choice('assistant.headline.unread', $notifications['unread'], ['count' => $notifications['unread']]), 'action' => ['type' => 'alerts', 'filter' => 'all', 'label' => __('assistant.show_alerts')], 'key' => 'unread-'.$notifications['unread']];
        }

        return null;
    }

    private function dayLabel(Carbon $at, string $tz): string
    {
        $d = $at->copy()->setTimezone($tz)->startOfDay();
        $today = now()->setTimezone($tz)->startOfDay();
        if ($d->equalTo($today)) {
            return __('assistant.today');
        }
        if ($d->equalTo($today->copy()->addDay())) {
            return __('assistant.tomorrow');
        }

        return $at->copy()->setTimezone($tz)->translatedFormat('l j/n');
    }

    /** @return Collection<int, array> */
    public static function flatten(array $briefing): Collection
    {
        return collect($briefing['meetings'])->concat($briefing['invitations']);
    }
}

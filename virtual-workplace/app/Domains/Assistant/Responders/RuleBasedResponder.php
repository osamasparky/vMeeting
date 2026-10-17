<?php

namespace App\Domains\Assistant\Responders;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Support\Str;

/**
 * No language model: recognises a handful of requests in Arabic or English by
 * keywords and answers from the person's briefing. Unknown requests get the
 * list of things it can do. Read-only — at most it offers buttons.
 */
class RuleBasedResponder implements AssistantResponder
{
    private const WORDS = [
        'help' => ['مساعده', 'ساعدني', 'تقدر', 'تستطيع', 'help', 'what can you'],
        'open' => ['افتح', 'انضم', 'ادخل', 'خذني', 'روح', 'join', 'open', 'take me', 'go to'],
        'meeting' => ['اجتماع', 'اجتماعات', 'اجتماعاتي', 'ميتنج', 'موعد', 'مواعيد', 'meeting', 'meetings', 'call', 'calendar'],
        'overdue' => ['متاخر', 'متاخره', 'المتاخره', 'متاخرات', 'فات', 'overdue', 'late', 'past due'],
        'task' => ['مهام', 'مهمه', 'مهمتي', 'مهامي', 'شغل', 'task', 'tasks', 'todo', 'to-do'],
        'alerts' => ['تنبيه', 'تنبيهات', 'اشعار', 'اشعارات', 'notification', 'notifications', 'alert', 'alerts'],
        'office' => ['مكتب', 'المكتب', 'office', 'room', 'غرفه'],
        'invite' => ['دعوه', 'دعوات', 'دعوني', 'invite', 'invites', 'invitation', 'invitations'],
        'message' => ['رساله', 'رسائل', 'رسايل', 'شات', 'message', 'messages', 'dm', 'chat'],
        'summary' => ['ملخص', 'لخص', 'يومي', 'جديد', 'summary', 'brief', "what's new", 'whats new', 'today'],
    ];

    public function respond(string $message, User $user, OrganizationMember $membership, array $briefing): array
    {
        $text = self::normalize($message);
        $has = fn (string $group) => collect(self::WORDS[$group])->contains(fn ($w) => str_contains($text, self::normalize($w)));

        if ($has('open') && $has('meeting')) {
            return $this->openMeeting($text, $briefing);
        }
        if ($has('alerts')) {
            $filter = $has('office') ? 'office' : ($has('meeting') ? 'meeting' : ($has('task') ? 'task' : 'all'));

            return $this->answer('alerts', __('assistant.answer.alerts.'.$filter), [], [], ['type' => 'panel', 'tab' => 'alerts', 'filter' => $filter]);
        }
        if ($has('invite')) {
            $items = array_map(fn ($m) => $this->meetingItem($m), $briefing['invitations']);

            return $this->answer('invitations', $items ? __('assistant.answer.invitations') : __('assistant.answer.no_invitations'), $items);
        }
        if ($has('overdue')) {
            $items = array_map(fn ($t) => $this->taskItem($t), $briefing['tasks']['overdue']);

            return $this->answer('overdue', $items ? trans_choice('assistant.answer.overdue', $briefing['tasks']['overdue_count'], ['count' => $briefing['tasks']['overdue_count']]) : __('assistant.answer.no_overdue'), $items);
        }
        if ($has('meeting')) {
            $items = array_map(fn ($m) => $this->meetingItem($m), $briefing['meetings']);

            return $this->answer('meetings', $items ? __('assistant.answer.meetings') : __('assistant.answer.no_meetings'), $items);
        }
        if ($has('task')) {
            $items = array_map(fn ($t) => $this->taskItem($t), array_merge($briefing['tasks']['overdue'], $briefing['tasks']['today']));

            return $this->answer('tasks', $items ? __('assistant.answer.tasks') : __('assistant.answer.no_tasks'), $items);
        }
        if ($has('message')) {
            $items = array_map(fn ($m) => [
                'kind' => 'message', 'title' => $m['from'], 'subtitle' => $m['preview'], 'meta' => $m['ago'], 'action' => $m['action'],
            ], $briefing['messages']);

            return $this->answer('messages', $items ? __('assistant.answer.messages') : __('assistant.answer.no_messages'), $items);
        }
        if ($has('summary')) {
            return $this->answer('summary', $briefing['headline']['text'] ?? __('assistant.answer.all_clear'), [], [], ['type' => 'panel', 'tab' => 'summary']);
        }
        if ($has('help')) {
            return $this->answer('help', __('assistant.answer.help'));
        }

        return $this->answer('unknown', __('assistant.answer.unknown'));
    }

    /** "Open the team meeting": the best title match among the person's own meetings, offered as a button. */
    private function openMeeting(string $text, array $briefing): array
    {
        $all = array_merge($briefing['meetings'], $briefing['invitations']);
        if (! $all) {
            return $this->answer('open_meeting', __('assistant.answer.no_meetings'));
        }
        $words = array_filter(preg_split('/\s+/u', $text), fn ($w) => mb_strlen($w) > 2);
        $scored = collect($all)->map(function ($m) use ($words) {
            $title = self::normalize($m['title']);
            $score = collect($words)->filter(fn ($w) => str_contains($title, $w))->count();

            return ['m' => $m, 'score' => $score];
        })->sortByDesc('score')->values();

        $best = $scored->first();
        $pick = $best['score'] > 0 ? $best['m'] : (count($all) === 1 ? $all[0] : null);
        if (! $pick) {
            return $this->answer('open_meeting', __('assistant.answer.which_meeting'), array_map(fn ($m) => $this->meetingItem($m), $all));
        }

        return $this->answer('open_meeting', __('assistant.answer.open_meeting', ['title' => $pick['title']]), [$this->meetingItem($pick)]);
    }

    private function meetingItem(array $m): array
    {
        $when = $m['status'] === 'active' ? __('assistant.live_now') : trim(($m['day'] ?? '').' '.($m['time'] ?? ''));

        return [
            'kind' => 'meeting',
            'title' => $m['title'],
            'subtitle' => collect([$m['room'], $m['invited'] && $m['organizer'] ? __('assistant.from', ['name' => $m['organizer']]) : null])->filter()->implode(' · '),
            'meta' => $when,
            'live' => $m['status'] === 'active',
            'action' => $m['action'],
        ];
    }

    private function taskItem(array $t): array
    {
        return [
            'kind' => 'task',
            'title' => $t['title'],
            'subtitle' => $t['project'],
            'meta' => $t['due'],
            'overdue' => $t['overdue'],
            'action' => $t['action'],
        ];
    }

    private function answer(string $intent, string $text, array $items = [], array $suggestions = [], ?array $action = null): array
    {
        return [
            'intent' => $intent,
            'text' => $text,
            'items' => $items,
            'action' => $action,
            'suggestions' => $suggestions ?: self::suggestions(),
        ];
    }

    /** The example requests offered as one-tap chips. */
    public static function suggestions(): array
    {
        return [__('assistant.suggest.meetings'), __('assistant.suggest.overdue'), __('assistant.suggest.office_alerts'), __('assistant.suggest.messages')];
    }

    /** Lower-case, no Arabic diacritics or tatweel, unify alef/yeh/teh-marbuta forms. */
    public static function normalize(string $s): string
    {
        $s = Str::lower(trim($s));
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $s);

        return strtr($s, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ي', 'ة' => 'ه', 'ؤ' => 'و', 'ئ' => 'ي', '؟' => ' ', '?' => ' ', '،' => ' ', ',' => ' ']);
    }
}

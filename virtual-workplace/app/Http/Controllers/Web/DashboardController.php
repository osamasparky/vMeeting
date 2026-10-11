<?php

namespace App\Http\Controllers\Web;

use App\Domains\Administration\Models\Role;
use App\Domains\Meetings\Actions\ScheduleMeetingAction;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Meetings\Requests\ScheduleMeetingRequest;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\People\Services\WorkspacePulse;
use App\Domains\Tenancy\Actions\BuildOrganizationDashboardAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use App\Support\NotificationPreferences;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function dashboard(BuildOrganizationDashboardAction $buildDashboard)
    {
        $user = Auth::user();

        // If Super Admin accesses tenant dashboard without a tenant membership, route cleanly to Super Admin Dashboard
        if ($user->isSuperAdmin()) {
            $hasMembership = OrganizationMember::where('user_id', $user->id)
                ->whereIn('status', ['active', 'invited'])
                ->exists();
            if (! $hasMembership) {
                return redirect()->route('superadmin.dashboard');
            }
        }

        // Get the active/invited membership
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'organization.subscription', 'role'])
            ->first();

        if (! $membership) {
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            $firstOrg = Organization::first();
            if ($firstOrg) {
                $defaultRole = Role::where('organization_id', $firstOrg->id)->where('slug', 'company_admin')->first()
                    ?? Role::first();
                $membership = OrganizationMember::firstOrCreate(
                    ['user_id' => $user->id, 'organization_id' => $firstOrg->id],
                    ['role_id' => $defaultRole ? $defaultRole->id : 2, 'status' => 'active']
                );
                $membership->load(['organization.plan', 'organization.subscription', 'role']);
            } else {
                Auth::logout();

                return redirect()->route('login')->with('error', 'No active organization found.');
            }
        }

        return view('dashboard', $buildDashboard->execute($user, $membership));
    }

    /** Who is in the office now (the dashboard refreshes its online panel with this). */
    public function pulse(WorkspacePulse $pulse)
    {
        $membership = OrganizationMember::where('user_id', Auth::id())->whereIn('status', ['active', 'invited'])->with('organization')->first();
        abort_unless($membership, 403);
        $online = $pulse->onlineNow($membership->organization);

        return response()->json(['online' => $online, 'count' => $online->count()]);
    }

    /**
     * Schedule a new meeting from the dashboard, notifying and emailing
     * every relevant recipient.
     */
    public function storeScheduledMeeting(ScheduleMeetingRequest $request, ScheduleMeetingAction $scheduleMeeting)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            abort(403);
        }

        $result = $scheduleMeeting->execute($membership->organization, $user, $request->validated());
        $meeting = $result['meeting'];

        $emailMsg = $result['sent_count'] > 0 ? ' ('.__(':count invitations emailed', ['count' => $result['sent_count']]).')' : '';
        $successMsg = __('Meeting ":title" scheduled successfully!', ['title' => $meeting->title]).$emailMsg;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'meeting' => $meeting,
                'redirect_url' => "/dashboard#{$result['tab']}",
            ]);
        }

        return redirect("/dashboard#{$result['tab']}")->with('success', $successMsg);
    }

    /**
     * Cancel an upcoming scheduled meeting.
     */
    public function cancelMeeting(Meeting $meeting)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership || $meeting->organization_id !== $membership->organization_id) {
            abort(403);
        }

        $meeting->update(['status' => 'ended']);

        return back()->with('success', __('Meeting ":title" has been cancelled.', ['title' => $meeting->title]));
    }

    public function uploadChatAttachment(Request $request, Organization $organization)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // max 20MB
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: 'bin');

        // Reachable without a session (invited guests): apply the shared
        // executable/script-carrying upload denylist.
        if ($rejection = FileUploadService::rejectionReasonFor($uploadedFile, $extension)) {
            return response()->json(['message' => $rejection], 422);
        }

        $mime = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();
        $filename = 'chat_'.Str::uuid().'.'.$extension;
        $path = $uploadedFile->storeAs("public/chat_files/{$organization->id}", $filename);
        $url = Storage::url($path);

        return response()->json([
            'name' => $originalName,
            'url' => $url,
            'size' => $size,
            'mime_type' => $mime,
        ]);
    }

    /**
     * The user's notifications. Without ?page this is the bell's feed (latest 35);
     * with ?page it is the "All notifications" tab: 30 per page, with an optional
     * ?filter of unread, task, meeting or office.
     */
    public function getUserNotifications(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $unreadCount = WorkplaceNotification::forUser($user->id)->unread()->count();
        $present = fn ($n) => [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'body' => $n->body,
            'icon' => $n->icon ?: '🔔',
            'action_url' => $n->action_url,
            'is_read' => (bool) $n->is_read,
            'data' => $n->data,
            'created_at_human' => $n->created_at ? $n->created_at->diffForHumans() : '',
            'created_at' => $n->created_at ? $n->created_at->toISOString() : '',
        ];

        if (! $request->has('page')) {
            return response()->json([
                'unread_count' => $unreadCount,
                'notifications' => WorkplaceNotification::forUser($user->id)->orderByDesc('created_at')->limit(35)->get()->map($present),
            ]);
        }

        $filter = $request->validate(['filter' => ['nullable', 'in:all,unread,task,meeting,office']])['filter'] ?? 'all';
        $page = WorkplaceNotification::forUser($user->id)
            ->when($filter === 'unread', fn ($q) => $q->unread())
            ->when($filter === 'task', fn ($q) => $q->where('type', 'like', 'task%'))
            ->when($filter === 'meeting', fn ($q) => $q->where('type', 'like', 'meeting%'))
            ->when($filter === 'office', fn ($q) => $q->where(fn ($w) => $w->whereIn('type', NotificationPreferences::OFFICE_TYPES)->orWhere('type', 'like', 'room%')))
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => collect($page->items())->map($present),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'total' => $page->total(),
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markNotificationRead(Request $request, string $id)
    {
        $user = Auth::user();
        $notification = WorkplaceNotification::forUser($user->id)->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        $unreadCount = WorkplaceNotification::forUser($user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllNotificationsRead(Request $request)
    {
        $user = Auth::user();
        WorkplaceNotification::forUser($user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Clear / delete all notifications for current user.
     */
    public function clearAllNotifications(Request $request)
    {
        $user = Auth::user();
        WorkplaceNotification::forUser($user->id)->delete();

        return response()->json([
            'success' => true,
            'unread_count' => 0,
            'notifications' => [],
        ]);
    }
}

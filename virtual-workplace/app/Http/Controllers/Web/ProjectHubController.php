<?php

namespace App\Http\Controllers\Web;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Actions\BuildProjectHubViewAction;
use App\Domains\Projects\Actions\PostTaskCommentWithNotificationsAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectFile;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskAttachment;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectHubController extends Controller
{
    /**
     * Render the Enterprise Project Hub Dashboard.
     */
    public function show(Request $request, Project $project, BuildProjectHubViewAction $buildHubView)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'role.permissions'])
            ->first();

        if (! $membership || $project->organization_id !== $membership->organization_id) {
            abort(404);
        }

        return view('projects.hub', $buildHubView->execute($user, $membership, $project));
    }

    /**
     * Store/Upload a file asset attached to the project.
     */
    public function storeFile(Request $request, Project $project)
    {
        return $this->uploadProjectFile($request, $project);
    }

    /**
     * Upload a file/document to a Project.
     */
    public function uploadProjectFile(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getClientMimeType() ?: 'application/octet-stream';
        $ext = strtolower($uploadedFile->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION) ?: 'bin');

        if ($rejection = FileUploadService::rejectionReasonFor($uploadedFile, $ext)) {
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $rejection], 422)
                : back()->withErrors(['file' => $rejection]);
        }

        $destDir = public_path('uploads/projects/'.$project->id);
        $stored = FileUploadService::moveToPublicUploads($uploadedFile, $destDir, 'prj_'.$project->id, $ext);

        $file = $project->files()->create([
            'organization_id' => $project->organization_id,
            'project_id' => $project->id,
            'user_id' => $user->id,
            'file_name' => $originalName,
            'file_path' => $stored['path'],
            'file_url' => '/uploads/projects/'.$project->id.'/'.$stored['filename'],
            'file_size' => $stored['size'],
            'mime_type' => $mimeType,
            'category' => $request->input('category', 'general'),
            'description' => $request->input('description'),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('File uploaded successfully.'),
                'file' => $file->load('user:id,name,email'),
            ], 201);
        }

        return back()->with('success', __('File uploaded to project successfully.'));
    }

    /**
     * Delete a project file.
     */
    public function destroyFile(Project $project, ProjectFile $file)
    {
        return $this->deleteProjectFile($project, $file);
    }

    /**
     * Delete a project file (alias).
     */
    public function deleteProjectFile(Project $project, ProjectFile $file)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('organization_id', $project->organization_id)->where('user_id', $user->id)->first();

        $canDelete = $user->isSuperAdmin()
            || ($membership && $membership->role?->slug === 'company_admin')
            || ($project->manager_id === $user->id)
            || ($file->user_id === $user->id);

        if (! $canDelete) {
            abort(403, __('Unauthorized to delete this file.'));
        }

        if ($file->file_path && file_exists($file->file_path)) {
            @unlink($file->file_path);
        } elseif (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => __('File deleted.')]);
        }

        return redirect()->back()->with('success', __('File deleted successfully.'));
    }

    /**
     * Upload an attachment to a Task.
     */
    public function uploadTaskAttachment(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB
        ]);

        $user = Auth::user();
        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getClientMimeType() ?: 'application/octet-stream';
        $ext = strtolower($uploadedFile->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION) ?: 'bin');

        if ($rejection = FileUploadService::rejectionReasonFor($uploadedFile, $ext)) {
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $rejection], 422)
                : back()->withErrors(['file' => $rejection]);
        }

        $destDir = public_path('uploads/tasks/'.$task->id);
        $stored = FileUploadService::moveToPublicUploads($uploadedFile, $destDir, 'task_'.$task->id, $ext);

        $attachment = TaskAttachment::create([
            'organization_id' => $task->organization_id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'file_name' => $originalName,
            'file_path' => $stored['path'],
            'file_url' => '/uploads/tasks/'.$task->id.'/'.$stored['filename'],
            'file_size' => $stored['size'],
            'mime_type' => $mimeType,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Attachment uploaded successfully.'),
                'attachment' => $attachment->load('user:id,name,email'),
            ], 201);
        }

        return back()->with('success', __('Attachment uploaded.'));
    }

    /**
     * Delete a task attachment.
     */
    public function deleteTaskAttachment(Task $task, TaskAttachment $attachment)
    {
        if ($attachment->file_path && file_exists($attachment->file_path)) {
            @unlink($attachment->file_path);
        }
        $attachment->delete();

        return response()->json(['success' => true, 'message' => __('Attachment deleted.')]);
    }

    /**
     * Get task comments thread.
     */
    public function getTaskComments(Task $task): JsonResponse
    {
        $comments = $task->comments()->with('user:id,name,email')->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    /**
     * Post a comment on a project task.
     */
    public function addComment(Request $request, Task $task, PostTaskCommentWithNotificationsAction $postComment)
    {
        return $this->storeTaskComment($request, $task, $postComment);
    }

    /**
     * Store a comment on a Task with @mentions parsing and notifications.
     */
    public function storeTaskComment(Request $request, Task $task, PostTaskCommentWithNotificationsAction $postComment)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'body' => 'required|string|max:3000',
        ]);

        $comment = $postComment->execute($task, $user, $validated['body']);

        return response()->json([
            'success' => true,
            'message' => __('Comment posted.'),
            'comment' => $comment,
        ]);
    }

    /**
     * Whether the given user may approve/reject work on this task: a
     * Super Admin, a company admin, anyone with the tasks.assign
     * permission, or the task's project manager.
     */
    private function isTaskManager(Task $task, User $user): bool
    {
        $membership = OrganizationMember::where('organization_id', $task->organization_id)->where('user_id', $user->id)->first();

        return $user->isSuperAdmin()
            || ($membership && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign')))
            || ($task->project && $task->project->manager_id === $user->id);
    }

    /**
     * Approve a task as completed (by Project Manager / Admin).
     */
    public function approveTask(Request $request, Task $task)
    {
        $user = Auth::user();

        if (! $this->isTaskManager($task, $user)) {
            return response()->json(['message' => __('Only Project Managers or Admins can approve tasks.')], 403);
        }

        $task->update([
            'status' => 'done',
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'completed_at' => now(),
            'rejection_reason' => null,
        ]);

        // Auto-recalculate milestone and goals
        $task->milestone?->checkAndUpdateStatus();
        $task->project?->goals->each->recalculateProgress();

        if ($task->assignee_id && $task->assignee_id !== $user->id) {
            NotificationService::notifyCustom(
                $task->assignee_id,
                'task_approved',
                __('🎉 Task Approved: ":task" has been approved as Completed by :name', ['task' => $task->title, 'name' => $user->name]),
                __('Great work! Your task was reviewed and approved.'),
                ['task_id' => $task->id, 'project_id' => $task->project_id],
                $user->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => __('Task approved and marked as Completed!'),
            'task' => $task->fresh(['project', 'assignee', 'approver']),
        ]);
    }

    /**
     * Reject / Request changes on a task (by Project Manager / Admin).
     */
    public function rejectTask(Request $request, Task $task)
    {
        $user = Auth::user();

        if (! $this->isTaskManager($task, $user)) {
            return response()->json(['message' => __('Only Project Managers or Admins can review tasks.')], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $task->update([
            'status' => 'in_progress',
            'approval_status' => 'rejected',
            'approved_by' => $user->id,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Auto-recalculate milestone and goals
        $task->milestone?->checkAndUpdateStatus();
        $task->project?->goals->each->recalculateProgress();

        if ($task->assignee_id && $task->assignee_id !== $user->id) {
            NotificationService::notifyCustom(
                $task->assignee_id,
                'task_rejected',
                __('⚠️ Changes Requested on task ":task" by :name', ['task' => $task->title, 'name' => $user->name]),
                $validated['rejection_reason'],
                ['task_id' => $task->id, 'project_id' => $task->project_id],
                $user->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => __('Task returned for revisions.'),
            'task' => $task->fresh(['project', 'assignee']),
        ]);
    }
}

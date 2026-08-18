<?php

namespace App\Domains\Collaboration\Controllers;

use App\Domains\Collaboration\Models\Recording;
use App\Domains\Tenancy\Models\Organization;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecordingController extends Controller
{
    /**
     * List all recordings for an organization.
     */
    public function index(Organization $organization): JsonResponse
    {
        $recordings = Recording::where('organization_id', $organization->id)
            ->with(['user:id,name,email', 'room:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'recordings' => $recordings,
        ]);
    }

    /**
     * Store and upload a new meeting recording file.
     */
    public function store(Request $request, Organization $organization): JsonResponse
    {
        $request->validate([
            'video' => 'required|file|max:204800', // max 200MB
            'title' => 'nullable|string|max:255',
            'room_id' => 'nullable|string',
            'duration_seconds' => 'nullable|numeric',
        ]);

        $file = $request->file('video');
        $extension = $file->getClientOriginalExtension() ?: 'webm';
        $filename = 'recording_' . Str::uuid() . '.' . $extension;
        $path = $file->storeAs("public/recordings/{$organization->id}", $filename);

        $url = Storage::url($path);

        $recording = Recording::create([
            'organization_id' => $organization->id,
            'user_id' => $request->user()?->id,
            'room_id' => $request->input('room_id'),
            'title' => $request->input('title') ?: ('Session Recording — ' . now()->toFormattedDateString()),
            'file_path' => $path,
            'file_url' => $url,
            'file_size' => $file->getSize(),
            'duration_seconds' => (int) $request->input('duration_seconds', 0),
            'recorded_by_name' => $request->input('recorded_by_name') ?: ($request->user()?->name ?: 'Guest'),
        ]);

        return response()->json([
            'message' => 'Recording saved successfully',
            'recording' => $recording,
        ], 201);
    }

    /**
     * Delete a recording.
     */
    public function destroy(Organization $organization, Recording $recording): JsonResponse
    {
        if ($recording->organization_id !== $organization->id) {
            abort(403, 'Unauthorized');
        }

        if (Storage::exists($recording->file_path)) {
            Storage::delete($recording->file_path);
        }

        $recording->delete();

        return response()->json([
            'message' => 'Recording deleted successfully',
        ]);
    }
}

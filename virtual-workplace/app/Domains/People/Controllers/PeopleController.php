<?php

namespace App\Domains\People\Controllers;

use App\Domains\People\Models\Department;
use App\Domains\People\Models\Team;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PeopleController extends Controller
{
    // ── Departments ──

    public function listDepartments(Request $request, Organization $organization): JsonResponse
    {
        $departments = Department::forOrganization($organization->id)
            ->with('parent', 'children', 'teams')
            ->get();

        return response()->json(['departments' => $departments]);
    }

    public function createDepartment(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_department_id' => 'nullable|exists:departments,id',
        ]);

        $department = Department::create([
            'organization_id' => $organization->id,
            ...$validated,
        ]);

        return response()->json([
            'message' => 'Department created.',
            'department' => $department,
        ], 201);
    }

    // ── Teams ──

    public function listTeams(Request $request, Organization $organization): JsonResponse
    {
        $teams = Team::forOrganization($organization->id)
            ->with('department')
            ->get();

        return response()->json(['teams' => $teams]);
    }

    public function createTeam(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $team = Team::create([
            'organization_id' => $organization->id,
            ...$validated,
        ]);

        return response()->json([
            'message' => 'Team created.',
            'team' => $team->load('department'),
        ], 201);
    }
}

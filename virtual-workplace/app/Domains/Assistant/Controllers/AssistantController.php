<?php

namespace App\Domains\Assistant\Controllers;

use App\Domains\Assistant\AssistantBriefing;
use App\Domains\Assistant\Responders\AssistantResponder;
use App\Domains\Assistant\Responders\RuleBasedResponder;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * The office assistant: a personal briefing and answers to typed requests,
 * both read-only and limited to the signed-in member's own data in their
 * organization. Notifications themselves are read and marked through the
 * existing /api/notifications endpoints.
 */
class AssistantController extends Controller
{
    public function __construct(
        private readonly AssistantBriefing $briefing,
        private readonly AssistantResponder $responder,
    ) {}

    private function membership(): OrganizationMember
    {
        $m = OrganizationMember::forUser(Auth::id())->where('status', 'active')->first();
        abort_unless($m && ! Auth::user()->is_guest, 403);

        return $m;
    }

    public function briefing(): JsonResponse
    {
        $m = $this->membership();

        return response()->json($this->briefing->forMember(Auth::user(), $m) + ['suggestions' => RuleBasedResponder::suggestions()]);
    }

    public function ask(Request $request): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:300']]);
        $m = $this->membership();
        $briefing = $this->briefing->forMember(Auth::user(), $m);

        return response()->json($this->responder->respond($data['message'], Auth::user(), $m, $briefing));
    }
}

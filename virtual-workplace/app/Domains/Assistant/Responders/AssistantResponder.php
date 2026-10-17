<?php

namespace App\Domains\Assistant\Responders;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\OrganizationMember;

/**
 * Answers what a person types into the assistant.
 *
 * The bundled RuleBasedResponder recognises a few requests and answers from
 * the person's own briefing. A language-model responder can be bound in its
 * place later (AppServiceProvider), with one rule: it may only read the
 * briefing it is handed and may only return actions the client already knows
 * (open a URL, walk to a room, open a direct message, open a panel tab) —
 * never perform writes itself.
 *
 * @phpstan-type Answer array{intent: string, text: string, items: list<array>, suggestions: list<string>}
 */
interface AssistantResponder
{
    /**
     * @param  array  $briefing  AssistantBriefing::forMember() for this person
     * @return array{intent: string, text: string, items: list<array>, suggestions: list<string>}
     */
    public function respond(string $message, User $user, OrganizationMember $membership, array $briefing): array;
}

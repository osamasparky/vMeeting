<?php

namespace App\Domains\Workspace\Services;

use App\Domains\People\Models\AttendanceSession;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Room;

/**
 * Which local time a working day is counted in.
 *
 *  - An office's own timezone (floors.timezone).
 *  - An organization's: its default office's, else the organization setting, else UTC.
 *  - A member's: their home office's, else the office they were last in, else the organization's.
 *
 * Lookups are memoized for the life of the instance.
 */
class OfficeTimezone
{
    /** @var array<string, array<string, string>> organization id => [floor id => timezone] */
    protected array $offices = [];

    /** @var array<string, string> */
    protected array $defaults = [];

    /** @var array<string, string> */
    protected array $members = [];

    public function forOffice(?string $floorId, string $organizationId): string
    {
        return ($floorId ? ($this->officesOf($organizationId)[$floorId] ?? null) : null)
            ?? $this->forOrganization($organizationId);
    }

    public function forOrganization(?string $organizationId): string
    {
        if (! $organizationId) {
            return config('app.timezone');
        }

        if (! isset($this->defaults[$organizationId])) {
            $default = Floor::withoutGlobalScopes()->where('organization_id', $organizationId)
                ->orderByDesc('is_default')->orderBy('order')->value('timezone');
            $orgTz = Organization::whereKey($organizationId)->value('timezone');

            $this->defaults[$organizationId] = Floor::isValidTimezone($default) ? $default
                : (Floor::isValidTimezone($orgTz) ? $orgTz : config('app.timezone'));
        }

        return $this->defaults[$organizationId];
    }

    public function forSession(AttendanceSession $session): string
    {
        return $this->forOffice($session->floor_id, $session->organization_id);
    }

    public function forMember(string $userId, string $organizationId): string
    {
        return $this->members[$organizationId.':'.$userId] ??= $this->forOffice($this->officeIdForMember($userId, $organizationId), $organizationId);
    }

    /**
     * The office a member works to: their home office, else the office they
     * were last in, else the organization's default office.
     */
    public function officeIdForMember(string $userId, string $organizationId): ?string
    {
        $offices = $this->officesOf($organizationId);

        $home = OrganizationMember::where('organization_id', $organizationId)->where('user_id', $userId)->value('home_floor_id');
        if ($home && isset($offices[$home])) {
            return $home;
        }

        $last = AttendanceSession::withoutGlobalScopes()->where('organization_id', $organizationId)
            ->where('user_id', $userId)->whereNotNull('floor_id')
            ->latest('started_at')->value('floor_id');
        if ($last && isset($offices[$last])) {
            return $last;
        }

        return $this->defaultOfficeId($organizationId);
    }

    public function defaultOfficeId(string $organizationId): ?string
    {
        return Floor::withoutGlobalScopes()->where('organization_id', $organizationId)
            ->orderByDesc('is_default')->orderBy('order')->value('id');
    }

    /**
     * The office a session belongs to: the room's office when in a room,
     * otherwise the office the client says it is in, if it is one of the
     * organization's; otherwise the organization's default office.
     */
    public function resolveOfficeId(string $organizationId, ?string $roomId, ?string $floorId): ?string
    {
        if ($roomId) {
            $fromRoom = Room::withoutGlobalScopes()->where('rooms.id', $roomId)
                ->join('maps', 'maps.id', '=', 'rooms.map_id')
                ->where('maps.organization_id', $organizationId)
                ->value('maps.floor_id');
            if ($fromRoom) {
                return $fromRoom;
            }
        }

        $offices = $this->officesOf($organizationId);
        if ($floorId && isset($offices[$floorId])) {
            return $floorId;
        }

        return $this->defaultOfficeId($organizationId);
    }

    /** @return array<string, string> */
    protected function officesOf(string $organizationId): array
    {
        return $this->offices[$organizationId] ??= Floor::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->pluck('timezone', 'id')
            ->filter(fn ($tz) => Floor::isValidTimezone($tz))
            ->all();
    }
}

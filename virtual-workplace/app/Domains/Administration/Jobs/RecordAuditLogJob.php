<?php

namespace App\Domains\Administration\Jobs;

use App\Domains\Administration\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordAuditLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $organizationId,
        public ?string $actorId,
        public string $action,
        public ?string $targetType = null,
        public ?string $targetId = null,
        public ?array $metadata = null
    ) {}

    public function handle(): void
    {
        AuditLog::create([
            'organization_id' => $this->organizationId,
            'actor_id' => $this->actorId,
            'action' => $this->action,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'metadata' => $this->metadata,
        ]);
    }
}

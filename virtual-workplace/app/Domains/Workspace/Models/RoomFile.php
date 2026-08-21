<?php

namespace App\Domains\Workspace\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomFile extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'room_files';

    protected $fillable = [
        'organization_id',
        'room_id',
        'uploaded_by_user_id',
        'uploader_name',
        'name',
        'file_path',
        'file_url',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}

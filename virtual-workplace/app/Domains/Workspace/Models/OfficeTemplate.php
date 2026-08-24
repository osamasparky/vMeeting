<?php

namespace App\Domains\Workspace\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'office_templates';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'background_image_url',
        'width',
        'height',
        'tile_size',
        'layout_data',
        'rooms_data',
        'objects_data',
        'is_default',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'rooms_data' => 'array',
        'objects_data' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'tile_size' => 'integer',
    ];

    /**
     * User who authored or last modified this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the default system template or initialize one.
     */
    public static function getDefault(): self
    {
        $template = self::where('is_default', true)->first();

        if (!$template) {
            $template = self::create([
                'name' => 'المكتب الذكي المفتوح - Nanobanaba HQ Blueprint',
                'slug' => 'default-office',
                'description' => 'القالب الافتراضي المعتمد للشركات الجديدة مع 5 مناطق وغرف مجهزة.',
                'background_image_url' => '/images/office_floorplan.jpg',
                'width' => 32,
                'height' => 26,
                'tile_size' => 32,
                'is_default' => true,
                'is_active' => true,
                'layout_data' => [
                    'theme' => 'open_spatial_blueprint',
                    'background_image_url' => '/images/office_floorplan.jpg',
                    'wall_sign_text' => 'COLLABORATIVE SESSIONS HQ',
                    'boardroom_sign' => 'BOARD ROOM - 10 Seats',
                ],
                'rooms_data' => [
                    [
                        'name' => 'قاعة مجلس الإدارة - Board Room (10 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 10,
                        'color' => '#3F7D4F',
                        'bounds' => ['x' => 17, 'y' => 1, 'width' => 14, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'ركن العمل الجماعي - Collaborative Sessions HQ',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 12,
                        'color' => '#245C3A',
                        'bounds' => ['x' => 1, 'y' => 1, 'width' => 15, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'محطات العمل الفردية - Private Focus Pods',
                        'type' => 'private',
                        'access_mode' => 'public',
                        'capacity' => 4,
                        'color' => '#4F9B5F',
                        'bounds' => ['x' => 1, 'y' => 13, 'width' => 15, 'height' => 12],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'الركن التكنولوجي - Maker / Tech Workbenches',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 6,
                        'color' => '#D6A23A',
                        'bounds' => ['x' => 17, 'y' => 13, 'width' => 14, 'height' => 6],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'الاستقبال والجدار النباتي - Reception & Botanical Lounge',
                        'type' => 'reception',
                        'access_mode' => 'public',
                        'capacity' => 8,
                        'color' => '#4F9B5F',
                        'bounds' => ['x' => 17, 'y' => 20, 'width' => 14, 'height' => 5],
                        'metadata' => ['audio_isolation' => false],
                    ],
                ],
                'objects_data' => [],
            ]);
        }

        return $template;
    }
}

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
        'plan_id',
        'plan_slug',
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
     * Associated subscription plan if this is a plan-specific template.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Tenancy\Models\Plan::class, 'plan_id');
    }

    /**
     * User who authored or last modified this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get or initialize the tailored default office template for a specific subscription plan.
     */
    public static function getForPlan(?\App\Domains\Tenancy\Models\Plan $plan = null): self
    {
        $slug = $plan ? $plan->slug : 'free';
        $template = self::where('plan_slug', $slug)->first();

        if ($template) {
            return $template;
        }

        // Predefined Blueprint Designs tailored to each Plan Tier
        $designs = [
            'free' => [
                'name' => 'المكتب الافتراضي المصغر - Free Studio Blueprint',
                'slug' => 'template-plan-free',
                'plan_slug' => 'free',
                'description' => 'تصميم مكتب مدمج مخصص للباقة المجانية (3 غرف أساسية: قاعة اجتماعات، ركن تعاوني، كبسولة تركيز).',
                'background_image_url' => '/images/office_floorplan.jpg',
                'width' => 32,
                'height' => 26,
                'tile_size' => 32,
                'is_default' => false,
                'rooms_data' => [
                    [
                        'name' => 'قاعة الاجتماعات الرئيسية - Main Meeting Room (6 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 6,
                        'color' => '#3F7D4F',
                        'bounds' => ['x' => 17, 'y' => 1, 'width' => 14, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'الركن التعاوني والدردشة - Collab Lounge (4 Seats)',
                        'type' => 'lounge',
                        'access_mode' => 'public',
                        'capacity' => 4,
                        'color' => '#245C3A',
                        'bounds' => ['x' => 1, 'y' => 1, 'width' => 15, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'ركن التركيز الفردي - Focus & Quiet Pod (2 Seats)',
                        'type' => 'private',
                        'access_mode' => 'public',
                        'capacity' => 2,
                        'color' => '#4F9B5F',
                        'bounds' => ['x' => 1, 'y' => 13, 'width' => 15, 'height' => 12],
                        'metadata' => ['audio_isolation' => true],
                    ],
                ],
            ],
            'starter' => [
                'name' => 'جناح الشركات الناشئة - Starter Growth Suite',
                'slug' => 'template-plan-starter',
                'plan_slug' => 'starter',
                'description' => 'تصميم مكتب متكامل للفرق الناشئة والنمو السريع مع 6 مناطق متنوعة وقاعات نقاش.',
                'background_image_url' => '/images/office_floorplan.jpg',
                'width' => 32,
                'height' => 26,
                'tile_size' => 32,
                'is_default' => false,
                'rooms_data' => [
                    [
                        'name' => 'مجلس الإدارة - Executive Boardroom (10 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 10,
                        'color' => '#3F7D4F',
                        'bounds' => ['x' => 17, 'y' => 1, 'width' => 14, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'قاعة الفرق - Team Huddle Room (8 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 8,
                        'color' => '#245C3A',
                        'bounds' => ['x' => 1, 'y' => 1, 'width' => 15, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'غرفة العصف الذهني - Brainstorming Studio (6 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 6,
                        'color' => '#D6A23A',
                        'bounds' => ['x' => 1, 'y' => 13, 'width' => 15, 'height' => 6],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'كبسولة الاتصال 1 - Phone Booth A (2 Seats)',
                        'type' => 'private',
                        'access_mode' => 'public',
                        'capacity' => 2,
                        'color' => '#4F9B5F',
                        'bounds' => ['x' => 1, 'y' => 20, 'width' => 7, 'height' => 5],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'كبسولة الاتصال 2 - Phone Booth B (2 Seats)',
                        'type' => 'private',
                        'access_mode' => 'public',
                        'capacity' => 2,
                        'color' => '#4F9B5F',
                        'bounds' => ['x' => 9, 'y' => 20, 'width' => 7, 'height' => 5],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'ردهة الاستقبال والترحيب - Reception & Welcome Lounge',
                        'type' => 'reception',
                        'access_mode' => 'public',
                        'capacity' => 8,
                        'color' => '#3F7D4F',
                        'bounds' => ['x' => 17, 'y' => 13, 'width' => 14, 'height' => 12],
                        'metadata' => ['audio_isolation' => false],
                    ],
                ],
            ],
            'business' => [
                'name' => 'المقر التنفيذي للشركات - Business Enterprise HQ',
                'slug' => 'template-plan-business',
                'plan_slug' => 'business',
                'description' => 'مقر متكامل متعدد الأجنحة للشركات المتوسطة والكبرى مجهز بـ 8 قاعات ومدرج مؤتمرات.',
                'background_image_url' => '/images/office_floorplan.jpg',
                'width' => 32,
                'height' => 26,
                'tile_size' => 32,
                'is_default' => false,
                'rooms_data' => [
                    [
                        'name' => 'المدرج الرئيسي للمؤتمرات - Town Hall Auditorium (50 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 50,
                        'color' => '#245C3A',
                        'bounds' => ['x' => 1, 'y' => 1, 'width' => 15, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'قاعة مجلس الإدارة الكبرى - Grand Boardroom (16 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 16,
                        'color' => '#3F7D4F',
                        'bounds' => ['x' => 17, 'y' => 1, 'width' => 14, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'جناح الهندسة والتطوير - Engineering & Tech Wing',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 12,
                        'color' => '#D6A23A',
                        'bounds' => ['x' => 17, 'y' => 13, 'width' => 14, 'height' => 6],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'جناح المبيعات والتسويق - Growth & Sales Pit',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 10,
                        'color' => '#4F9B5F',
                        'bounds' => ['x' => 1, 'y' => 13, 'width' => 15, 'height' => 6],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'قاعة العملاء والشركاء - Client Partner Suite (8 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 8,
                        'color' => '#245C3A',
                        'bounds' => ['x' => 1, 'y' => 20, 'width' => 7, 'height' => 5],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'كبسولات الخصوصية والمكالمات - Focus & Phone Pods',
                        'type' => 'private',
                        'access_mode' => 'public',
                        'capacity' => 4,
                        'color' => '#4F9B5F',
                        'bounds' => ['x' => 9, 'y' => 20, 'width' => 7, 'height' => 5],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'الاستقبال الفندقي - Grand Executive Reception Desk',
                        'type' => 'reception',
                        'access_mode' => 'public',
                        'capacity' => 10,
                        'color' => '#3F7D4F',
                        'bounds' => ['x' => 17, 'y' => 20, 'width' => 14, 'height' => 5],
                        'metadata' => ['audio_isolation' => false],
                    ],
                ],
            ],
            'enterprise' => [
                'name' => 'المقر الشامل للشركات الكبرى - Mega Campus & Corporate HQ',
                'slug' => 'template-plan-enterprise',
                'plan_slug' => 'enterprise',
                'description' => 'المجمع الرقمي الفاخر والشامل للشركات العالمية والمؤسسات الحكومية والخاصة مع كافة المرافق.',
                'background_image_url' => '/images/office_floorplan.jpg',
                'width' => 32,
                'height' => 26,
                'tile_size' => 32,
                'is_default' => false,
                'rooms_data' => [
                    [
                        'name' => 'المدرج والمؤتمرات الكبرى - Mega Auditorium (100 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 100,
                        'color' => '#245C3A',
                        'bounds' => ['x' => 1, 'y' => 1, 'width' => 15, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'مجلس الإدارة التنفيذي - VIP Executive Boardroom (20 Seats)',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 20,
                        'color' => '#3F7D4F',
                        'bounds' => ['x' => 17, 'y' => 1, 'width' => 14, 'height' => 11],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'جناح الابتكار والتقنية - Innovation & Cloud Center',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 15,
                        'color' => '#D6A23A',
                        'bounds' => ['x' => 17, 'y' => 13, 'width' => 14, 'height' => 6],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'جناح القيادة والعمليات - Leadership & Global Ops Hub',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 12,
                        'color' => '#4F9B5F',
                        'bounds' => ['x' => 1, 'y' => 13, 'width' => 15, 'height' => 6],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'صالون كبار الشخصيات - VIP Networking Club (10 Seats)',
                        'type' => 'lounge',
                        'access_mode' => 'public',
                        'capacity' => 10,
                        'color' => '#245C3A',
                        'bounds' => ['x' => 1, 'y' => 20, 'width' => 7, 'height' => 5],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'استوديو الإعلام والبث - Broadcast & Podcast Studio',
                        'type' => 'meeting',
                        'access_mode' => 'public',
                        'capacity' => 6,
                        'color' => '#D6A23A',
                        'bounds' => ['x' => 9, 'y' => 20, 'width' => 7, 'height' => 5],
                        'metadata' => ['audio_isolation' => true],
                    ],
                    [
                        'name' => 'الاستقبال الرئيسي للشركات - Corporate Grand Reception',
                        'type' => 'reception',
                        'access_mode' => 'public',
                        'capacity' => 12,
                        'color' => '#3F7D4F',
                        'bounds' => ['x' => 17, 'y' => 20, 'width' => 14, 'height' => 5],
                        'metadata' => ['audio_isolation' => false],
                    ],
                ],
            ],
        ];

        $targetDesign = $designs[$slug] ?? $designs['free'];
        $planModel = $plan ?: \App\Domains\Tenancy\Models\Plan::where('slug', $slug)->first();

        return self::create([
            'name' => $targetDesign['name'],
            'slug' => $targetDesign['slug'],
            'plan_id' => $planModel?->id,
            'plan_slug' => $slug,
            'description' => $targetDesign['description'],
            'background_image_url' => $targetDesign['background_image_url'],
            'width' => $targetDesign['width'],
            'height' => $targetDesign['height'],
            'tile_size' => $targetDesign['tile_size'],
            'is_default' => ($slug === 'free'),
            'is_active' => true,
            'layout_data' => [
                'theme' => 'open_spatial_blueprint',
                'background_image_url' => $targetDesign['background_image_url'],
            ],
            'rooms_data' => $targetDesign['rooms_data'],
            'objects_data' => [],
        ]);
    }

    /**
     * Get the default system template (Free tier default).
     */
    public static function getDefault(): self
    {
        return self::getForPlan(null);
    }
}

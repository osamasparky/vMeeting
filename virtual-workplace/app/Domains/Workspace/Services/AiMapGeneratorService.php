<?php

namespace App\Domains\Workspace\Services;

use App\Domains\Administration\Models\SystemSetting;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AiMapGeneratorService
{
    /**
     * Architectural style descriptions for DALL-E 3 prompt engineering.
     */
    protected array $styles = [
        'modern_glass_luxury' => [
            'name' => 'Modern Glass Luxury',
            'name_ar' => 'زجاجي حديث فاخر',
            'description' => 'Ultra-modern luxury corporate office with floor-to-ceiling soundproof glass walls, polished marble and dark oak wood flooring, minimalist black steel framing, warm ambient recessed LED perimeter lighting, panoramic executive skyline feel.',
            'primary_color' => '#10B981',
        ],
        'scandinavian_minimalist' => [
            'name' => 'Scandinavian Minimalist',
            'name_ar' => 'اسكندنافي بسيط وهادئ',
            'description' => 'Nordic Scandinavian open-plan studio with light natural ash wood, acoustic felt wall panels, soft beige and white terrazzo flooring, abundant biophilic indoor plants, clean airy natural daylight.',
            'primary_color' => '#3B82F6',
        ],
        'silicon_valley_tech' => [
            'name' => 'Silicon Valley Tech HQ',
            'name_ar' => 'مقر تقني على طراز وادي السيليكون',
            'description' => 'High-tech Silicon Valley startup headquarters with exposed industrial brick and matte black steel beams, polished concrete floors, vibrant accent neon stripes, agile glass collaboration pods.',
            'primary_color' => '#8B5CF6',
        ],
        'executive_corporate' => [
            'name' => 'Executive Corporate Board',
            'name_ar' => 'رئاسي تنفيذي كلاسيكي',
            'description' => 'Prestigious executive corporate headquarters with rich mahogany paneling, deep navy and gold accents, plush acoustic carpet tiles, frosted glass boardroom partitions, stately executive furniture.',
            'primary_color' => '#D97706',
        ],
        'warm_wood_botanical' => [
            'name' => 'Biophilic Warm Wood & Botanical',
            'name_ar' => 'طبيعي دافئ مع نباتات خضراء',
            'description' => 'Eco-luxury biophilic workspace featuring natural live-edge timber tables, lush vertical living green walls, sunlit skylights, organic terracotta and travertine textures, serene tranquil atmosphere.',
            'primary_color' => '#059669',
        ],
        'cyberpunk_neon' => [
            'name' => 'Cyberpunk Dark Neon Studio',
            'name_ar' => 'استوديو مستقبلي مظلم بإضاءات نيون',
            'description' => 'Futuristic cyberpunk creative agency office with dark obsidian surfaces, glowing emerald and cyan neon architectural trim, holographic glass displays, dark metallic textures.',
            'primary_color' => '#EC4899',
        ],
    ];

    /**
     * Generate an AI-powered Virtual Office Blueprint & Spatial Rooms layout.
     *
     * @param Organization $organization
     * @param array $options
     * @param User|null $user
     * @return array
     * @throws ValidationException
     */
    public function generate(Organization $organization, array $options, ?User $user = null): array
    {
        $plan = $organization->plan;

        // 1. Parse room quantities
        $meetingRoomsCount = max(0, min(6, (int)($options['meeting_rooms'] ?? 1)));
        $officeRoomsCount = max(1, min(8, (int)($options['office_rooms'] ?? 2)));
        $desksPerOffice = max(1, min(12, (int)($options['desks_per_office'] ?? 4)));
        $thinkingRoomsCount = max(0, min(4, (int)($options['thinking_rooms'] ?? 1)));
        $restAreasCount = max(0, min(3, (int)($options['rest_areas'] ?? 1)));
        $theatersCount = max(0, min(2, (int)($options['theaters'] ?? 0)));
        $styleKey = $options['style'] ?? 'modern_glass_luxury';
        $styleConfig = $this->styles[$styleKey] ?? $this->styles['modern_glass_luxury'];

        // Mandatory rooms: Coffee Corner (1) + Reception Lobby (1)
        $coffeeCount = 1;
        $receptionCount = 1;

        $totalRooms = $meetingRoomsCount + $officeRoomsCount + $thinkingRoomsCount + $restAreasCount + $theatersCount + $coffeeCount + $receptionCount;
        $totalDesks = ($officeRoomsCount * $desksPerOffice) + ($meetingRoomsCount * 6) + ($thinkingRoomsCount * 4) + ($restAreasCount * 6) + ($theatersCount * 16) + 4;

        // 2. Validate against Subscription Plan limits
        if ($plan && $plan->room_limit > 0 && $totalRooms > $plan->room_limit) {
            throw ValidationException::withMessages([
                'rooms' => __("Your current subscription plan (:plan) allows up to :limit rooms. You requested :total rooms. Please upgrade your plan or reduce room counts.", [
                    'plan' => $plan->name,
                    'limit' => $plan->room_limit,
                    'total' => $totalRooms,
                ]),
            ]);
        }

        if ($plan && $plan->seat_limit > 0 && $totalDesks > $plan->seat_limit) {
            throw ValidationException::withMessages([
                'seats' => __("Your current subscription plan (:plan) allows up to :limit members/seats. Your proposed office capacity is :total seats. Please adjust desk counts.", [
                    'plan' => $plan->name,
                    'limit' => $plan->seat_limit,
                    'total' => $totalDesks,
                ]),
            ]);
        }

        // 3. Obtain OpenAI Configuration
        $aiSettings = SystemSetting::get('openai_settings', [
            'api_key' => env('OPENAI_API_KEY', ''),
            'model' => 'dall-e-3',
            'image_size' => '1792x1024',
            'quality' => 'standard',
            'prompt_prefix' => 'A clean, high-angle photorealistic 3D isometric architectural floorplan of a modern virtual workplace office.',
            'is_enabled' => true,
        ]);

        $apiKey = !empty($aiSettings['api_key']) ? trim($aiSettings['api_key']) : env('OPENAI_API_KEY', '');
        $model = $aiSettings['model'] ?? 'dall-e-3';
        $imageSize = $aiSettings['image_size'] ?? '1792x1024';
        $quality = $aiSettings['quality'] ?? 'standard';
        $promptPrefix = $aiSettings['prompt_prefix'] ?? 'A clean, high-angle photorealistic 3D isometric architectural floorplan of a modern virtual workplace office.';

        // 4. Construct Architectural Prompt
        $roomSummaryParts = [];
        $roomSummaryParts[] = "1 Grand Reception & Welcome Lobby at entrance";
        $roomSummaryParts[] = "1 Central Coffee Bar Lounge & Cafeteria";
        if ($meetingRoomsCount > 0) $roomSummaryParts[] = "{$meetingRoomsCount} Glass-walled Meeting Boardrooms with conference table";
        if ($officeRoomsCount > 0) $roomSummaryParts[] = "{$officeRoomsCount} Executive & Team Workspaces with {$desksPerOffice} computer desks each";
        if ($thinkingRoomsCount > 0) $roomSummaryParts[] = "{$thinkingRoomsCount} Creative Thinking & Brainstorming Pods";
        if ($restAreasCount > 0) $roomSummaryParts[] = "{$restAreasCount} Relaxation & Gaming Breakout Lounge";
        if ($theatersCount > 0) $roomSummaryParts[] = "{$theatersCount} Presentation Amphitheater / Auditorium Stage";

        $roomsText = implode(', ', $roomSummaryParts);

        $prompt = "{$promptPrefix} 3D isometric architectural floorplan view (orthographic perspective, high angle 45-degree isometric projection) of an entire virtual office layout in {$styleConfig['description']}. Clear modular room partitions with distinct functional zones: {$roomsText}. Clean architectural layout, clear floor plan zoning, top-down isometric view, hyper-detailed interior 3D rendering, no people.";

        // 5. Generate or Obtain Blueprint Artwork
        $imageUrl = null;
        $isMock = false;

        if (!empty($apiKey) && !empty($aiSettings['is_enabled'])) {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model' => $model,
                        'prompt' => $prompt,
                        'n' => 1,
                        'size' => $imageSize,
                        'quality' => $quality,
                        'response_format' => 'url',
                    ]);

                if ($response->successful()) {
                    $openAiData = $response->json();
                    $remoteImageUrl = $openAiData['data'][0]['url'] ?? null;

                    if ($remoteImageUrl) {
                        $imageBinary = Http::timeout(60)->get($remoteImageUrl)->body();
                        $filename = 'ai_map_' . $organization->id . '_' . time() . '.png';
                        $destinationDir = public_path('uploads/maps');
                        if (!File::isDirectory($destinationDir)) {
                            File::makeDirectory($destinationDir, 0777, true, true);
                        }
                        File::put($destinationDir . '/' . $filename, $imageBinary);
                        $imageUrl = '/uploads/maps/' . $filename;
                    }
                } else {
                    Log::warning('OpenAI Image Generation Error: ' . $response->body());
                    $imageUrl = '/images/office_floorplan.jpg';
                    $isMock = true;
                }
            } catch (\Throwable $e) {
                Log::error('OpenAI Request Failed: ' . $e->getMessage());
                $imageUrl = '/images/office_floorplan.jpg';
                $isMock = true;
            }
        } else {
            $imageUrl = '/images/office_floorplan.jpg';
            $isMock = true;
        }

        // 6. Compute Geometric Spatial Layout & Isolated Room Boundaries (Grid 48x32 tiles)
        $gridWidth = 48;
        $gridHeight = 32;
        $tileSize = 16;

        $roomBlueprints = $this->computeSpatialLayout([
            'meeting_rooms' => $meetingRoomsCount,
            'office_rooms' => $officeRoomsCount,
            'desks_per_office' => $desksPerOffice,
            'thinking_rooms' => $thinkingRoomsCount,
            'rest_areas' => $restAreasCount,
            'theaters' => $theatersCount,
        ], $gridWidth, $gridHeight);

        // 7. Persist to Database (Floor, Map, Rooms)
        $targetFloorId = $options['target_floor_id'] ?? null;
        $floor = null;

        if ($targetFloorId) {
            $floor = $organization->floors()->find($targetFloorId);
        }

        if (!$floor) {
            $floorName = !empty($options['floor_name']) ? trim($options['floor_name']) : ($styleConfig['name'] . ' ' . __('Office'));
            $isFirstFloor = ($organization->floors()->count() === 0);
            $floor = $organization->floors()->create([
                'name' => $floorName,
                'is_default' => $isFirstFloor,
                'order' => $organization->floors()->max('order') + 1,
            ]);
        }

        // Get or Create Map
        $map = $organization->maps()->where('floor_id', $floor->id)->first();
        if (!$map) {
            $map = $organization->maps()->create([
                'floor_id' => $floor->id,
                'name' => $floor->name . ' AI Blueprint',
                'status' => 'published',
                'version' => 1,
                'width' => $gridWidth,
                'height' => $gridHeight,
                'tile_size' => $tileSize,
                'layout_data' => [
                    'theme' => $styleKey,
                    'style_name' => $styleConfig['name'],
                    'background_image_url' => $imageUrl,
                    'wall_sign_text' => strtoupper($floor->name),
                    'is_ai_generated' => true,
                    'generated_at' => now()->toIso8601String(),
                ],
                'published_at' => now(),
            ]);
        } else {
            $layoutData = $map->layout_data ?? [];
            $layoutData['theme'] = $styleKey;
            $layoutData['style_name'] = $styleConfig['name'];
            $layoutData['background_image_url'] = $imageUrl;
            $layoutData['is_ai_generated'] = true;
            $layoutData['generated_at'] = now()->toIso8601String();

            $map->update([
                'width' => $gridWidth,
                'height' => $gridHeight,
                'layout_data' => $layoutData,
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        // Delete old rooms for this map and populate newly calculated isolated rooms
        $map->rooms()->delete();

        $createdRooms = [];
        foreach ($roomBlueprints as $rb) {
            $room = Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => $rb['name'],
                'type' => $rb['type'],
                'access_mode' => 'public',
                'capacity' => $rb['capacity'],
                'color' => $rb['color'],
                'bounds' => $rb['bounds'],
                'metadata' => [
                    'desks_count' => $rb['desks_count'] ?? 0,
                    'description' => $rb['description'] ?? '',
                    'ai_generated' => true,
                    'spawn' => [
                        'x' => round(($rb['bounds']['x'] + ($rb['bounds']['width'] / 2)) * $tileSize),
                        'y' => round(($rb['bounds']['y'] + ($rb['bounds']['height'] / 2)) * $tileSize),
                    ],
                ],
            ]);
            $createdRooms[] = $room;
        }

        return [
            'success' => true,
            'floor' => $floor,
            'map' => $map->fresh(['rooms']),
            'rooms_count' => count($createdRooms),
            'background_image_url' => $imageUrl,
            'is_mock' => $isMock,
            'message' => $isMock
                ? __('AI Blueprint layout created using default visual studio (OpenAI API key not configured or rate-limited).')
                : __('AI Workplace & Floorplan Blueprint generated successfully via OpenAI DALL-E 3!'),
        ];
    }

    /**
     * Mathematically partitions the 48x32 canvas into isolated functional rooms.
     */
    protected function computeSpatialLayout(array $config, int $gridWidth = 48, int $gridHeight = 32): array
    {
        $rooms = [];

        // 1. Reception & Welcome Lobby (Bottom-Center: entrance)
        $rooms[] = [
            'name' => __('Reception & Welcome Lobby (الاستقبال والمدخل)'),
            'type' => 'common',
            'capacity' => 6,
            'color' => '#10B981',
            'bounds' => [
                'x' => 17,
                'y' => 24,
                'width' => 14,
                'height' => 7,
            ],
            'desks_count' => 2,
            'description' => 'Main workplace reception desk, check-in kiosk, and greeting area.',
        ];

        // 2. Coffee Bar & Cafeteria (Central Social Hub)
        $rooms[] = [
            'name' => __('Coffee Corner & Cafeteria (ركن القهوة والاستراحة)'),
            'type' => 'breakout',
            'capacity' => 10,
            'color' => '#F59E0B',
            'bounds' => [
                'x' => 18,
                'y' => 13,
                'width' => 12,
                'height' => 8,
            ],
            'desks_count' => 4,
            'description' => 'Central espresso bar, high-tables, water cooler, and social lounge.',
        ];

        // 3. Executive / Team Offices (West Wing: x: 2..15)
        $officeCount = $config['office_rooms'] ?? 2;
        $desksPerOffice = $config['desks_per_office'] ?? 4;
        $westAvailableHeight = 28; // y: 2 to 30
        $officeHeight = max(5, floor($westAvailableHeight / max(1, $officeCount)));

        for ($i = 0; $i < $officeCount; $i++) {
            $y = 2 + ($i * $officeHeight);
            $h = min($officeHeight - 1, 30 - $y);
            if ($h < 4) continue;

            $num = $i + 1;
            $rooms[] = [
                'name' => __("Team Workspace (مكتب فريق) :num", ['num' => $num]),
                'type' => 'office',
                'capacity' => $desksPerOffice,
                'color' => '#3B82F6',
                'bounds' => [
                    'x' => 2,
                    'y' => $y,
                    'width' => 13,
                    'height' => $h,
                ],
                'desks_count' => $desksPerOffice,
                'description' => "Dedicated workspace equipped with {$desksPerOffice} workstations.",
            ];
        }

        // 4. Meeting Boardrooms (East Wing: x: 33..46)
        $meetingCount = $config['meeting_rooms'] ?? 1;
        if ($meetingCount > 0) {
            $meetingAvailableHeight = 18; // y: 2 to 20
            $meetingHeight = max(6, floor($meetingAvailableHeight / $meetingCount));

            for ($i = 0; $i < $meetingCount; $i++) {
                $y = 2 + ($i * $meetingHeight);
                $h = min($meetingHeight - 1, 22 - $y);
                if ($h < 5) continue;

                $num = $i + 1;
                $rooms[] = [
                    'name' => __("Meeting Boardroom (قاعة اجتماعات) :num", ['num' => $num]),
                    'type' => 'meeting',
                    'capacity' => 8,
                    'color' => '#8B5CF6',
                    'bounds' => [
                        'x' => 33,
                        'y' => $y,
                        'width' => 13,
                        'height' => $h,
                    ],
                    'desks_count' => 6,
                    'description' => 'Soundproof conference room with smart screen and boardroom table.',
                ];
            }
        }

        // 5. Thinking & Brainstorming Pods (North-Center: y: 2..10)
        $thinkingCount = $config['thinking_rooms'] ?? 0;
        if ($thinkingCount > 0) {
            $rooms[] = [
                'name' => __('Thinking & Focus Pod (غرفة التركيز والعصف الذهني)'),
                'type' => 'focus',
                'capacity' => 4,
                'color' => '#06B6D4',
                'bounds' => [
                    'x' => 18,
                    'y' => 2,
                    'width' => 12,
                    'height' => 8,
                ],
                'desks_count' => 3,
                'description' => 'Quiet acoustic focus sanctuary with whiteboards and ideation boards.',
            ];
        }

        // 6. Rest & Gaming Lounge (South-East: x: 33..46, y: 22..30)
        $restCount = $config['rest_areas'] ?? 0;
        if ($restCount > 0) {
            $rooms[] = [
                'name' => __('Rest & Gaming Lounge (صالة الراحة والترفيه)'),
                'type' => 'breakout',
                'capacity' => 8,
                'color' => '#EC4899',
                'bounds' => [
                    'x' => 33,
                    'y' => 22,
                    'width' => 13,
                    'height' => 8,
                ],
                'desks_count' => 6,
                'description' => 'Cozy lounge couches, arcade / gaming station, and bean bags.',
            ];
        }

        // 7. Presentation Theater / Stage (North-East or Top Wing)
        $theaterCount = $config['theaters'] ?? 0;
        if ($theaterCount > 0) {
            $rooms[] = [
                'name' => __('Presentation Theater & Stage (مسرح وقاعة المؤتمرات)'),
                'type' => 'stage',
                'capacity' => 20,
                'color' => '#E11D48',
                'bounds' => [
                    'x' => 31,
                    'y' => 2,
                    'width' => 15,
                    'height' => 10,
                ],
                'desks_count' => 16,
                'description' => 'Auditorium amphitheater with keynote presentation screen and rows of seats.',
            ];
        }

        return $rooms;
    }

    /**
     * Get available architectural styles list.
     */
    public function getStyles(): array
    {
        return $this->styles;
    }
}

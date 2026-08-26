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
            'name' => 'Modern Glass Luxury (2D Floorplan)',
            'name_ar' => 'زجاجي حديث فاخر (مخطط أفقي 2D)',
            'description' => 'Ultra-modern luxury corporate aesthetic with soundproof glass partition walls, polished concrete hallways, dark oak wood office flooring, emerald acoustic boardroom carpet, minimalist black steel framing, soft warm circular ceiling downlights.',
            'primary_color' => '#10B981',
        ],
        'scandinavian_minimalist' => [
            'name' => 'Scandinavian Minimalist (2D Floorplan)',
            'name_ar' => 'اسكندنافي هادئ (مخطط أفقي 2D)',
            'description' => 'Nordic Scandinavian aesthetic with light ash hardwood parquet floors, beige terrazzo walkways, acoustic felt divider walls, soft pastel rugs, clean airy lighting and abundant potted indoor plants.',
            'primary_color' => '#3B82F6',
        ],
        'silicon_valley_tech' => [
            'name' => 'Silicon Valley Tech Studio (2D Floorplan)',
            'name_ar' => 'استوديو تقني وادي السيليكون (مخطط أفقي 2D)',
            'description' => 'High-tech Silicon Valley startup aesthetic with industrial grey screed concrete, matte black steel wall dividers, agile collaboration zones with modern dual-monitor workstations and neon perimeter trim.',
            'primary_color' => '#8B5CF6',
        ],
        'executive_corporate' => [
            'name' => 'Executive Corporate Board (2D Floorplan)',
            'name_ar' => 'رئاسي تنفيذي كلاسيكي (مخطط أفقي 2D)',
            'description' => 'Prestigious executive corporate aesthetic with rich mahogany wood partitions, navy and gold patterned acoustic carpet tiles, frosted glass meeting suites, and stately executive desks.',
            'primary_color' => '#D97706',
        ],
        'warm_wood_botanical' => [
            'name' => 'Biophilic Warm Wood & Botanical (2D Floorplan)',
            'name_ar' => 'طبيعي دافئ مع نباتات (مخطط أفقي 2D)',
            'description' => 'Eco-luxury biophilic workspace with live-edge natural timber flooring, lush green planter dividing walls, travertine reception tiles, and sunlit organic atmosphere.',
            'primary_color' => '#059669',
        ],
        'cyberpunk_neon' => [
            'name' => 'Cyberpunk Dark Studio (2D Floorplan)',
            'name_ar' => 'استوديو مظلم بإضاءات نيون (مخطط أفقي 2D)',
            'description' => 'Futuristic cyberpunk studio with dark charcoal obsidian flooring, glowing emerald and cyan recessed LED floor strips, dark acoustic theater carpet, and holographic monitors.',
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

        // 1. Parse room quantities safely
        $meetingRoomsCount = isset($options['meeting_rooms']) ? max(0, min(6, (int)$options['meeting_rooms'])) : 0;
        $officeRoomsCount = isset($options['office_rooms']) ? max(1, min(8, (int)$options['office_rooms'])) : 1;
        $desksPerOffice = isset($options['desks_per_office']) ? max(1, min(12, (int)$options['desks_per_office'])) : 1;
        $thinkingRoomsCount = isset($options['thinking_rooms']) ? max(0, min(4, (int)$options['thinking_rooms'])) : 0;
        $restAreasCount = isset($options['rest_areas']) ? max(0, min(3, (int)$options['rest_areas'])) : 0;
        $theatersCount = isset($options['theaters']) ? max(0, min(2, (int)$options['theaters'])) : 0;
        $styleKey = $options['style'] ?? 'modern_glass_luxury';
        $styleConfig = $this->styles[$styleKey] ?? $this->styles['modern_glass_luxury'];

        // Only count actual custom rooms chosen by user against plan room_limit
        // (Coffee Corner and Reception are free communal areas)
        $totalRooms = $meetingRoomsCount + $officeRoomsCount + $thinkingRoomsCount + $restAreasCount + $theatersCount;
        
        // Only count actual team office desks against plan seat_limit
        $totalDesks = ($officeRoomsCount * $desksPerOffice);

        // 2. Validate against Subscription Plan limits
        if ($plan && $plan->room_limit > 0 && $totalRooms > $plan->room_limit) {
            throw ValidationException::withMessages([
                'rooms' => __("Your current subscription plan (:plan) allows up to :limit rooms. You selected :total rooms. Please upgrade your plan or reduce room counts.", [
                    'plan' => $plan->name,
                    'limit' => $plan->room_limit,
                    'total' => $totalRooms,
                ]),
            ]);
        }

        if ($plan && $plan->seat_limit > 0 && $totalDesks > $plan->seat_limit) {
            throw ValidationException::withMessages([
                'seats' => __("Your current subscription plan (:plan) allows up to :limit members/seats. Your office has :total desks. Please reduce the number of desks or offices.", [
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
            'prompt_prefix' => "A clean, photorealistic direct top-down 2D architectural floor plan blueprint of a modern virtual workplace office (straight 90-degree overhead bird's-eye plan view with cutaway interior walls).",
            'is_enabled' => true,
        ]);

        $apiKey = !empty($aiSettings['api_key']) ? trim($aiSettings['api_key']) : env('OPENAI_API_KEY', '');
        $model = $aiSettings['model'] ?? 'dall-e-3';
        $imageSize = $aiSettings['image_size'] ?? '1792x1024';
        $quality = $aiSettings['quality'] ?? 'standard';
        $promptPrefix = $aiSettings['prompt_prefix'] ?? "A clean, photorealistic direct top-down 2D architectural floor plan blueprint of a modern virtual workplace office (straight 90-degree overhead bird's-eye plan view with cutaway interior walls).";

        // 4. Construct Highly Detailed & Tailored 2D Architectural Blueprint Prompt
        $roomSummaryParts = [];
        $roomSummaryParts[] = "1 Welcome Reception Lobby with reception desk, receptionist chair, and check-in computer";
        $roomSummaryParts[] = "1 Espresso Coffee Bar & Cafeteria Counter with barstools, high table, and water cooler";
        if ($meetingRoomsCount > 0) $roomSummaryParts[] = "{$meetingRoomsCount} Executive Meeting Boardroom(s) with central conference table, 8 to 10 executive chairs, acoustic carpet, and wall presentation display";
        if ($officeRoomsCount > 0) $roomSummaryParts[] = "{$officeRoomsCount} Dedicated Team Workspace Room(s) on warm hardwood flooring, each fitted with {$desksPerOffice} computer workstation desks and ergonomic chairs";
        if ($thinkingRoomsCount > 0) $roomSummaryParts[] = "{$thinkingRoomsCount} Creative Thinking / Brainstorming Pod(s) with comfortable armchairs, round coffee table, area rug, whiteboard, and bookshelves";
        if ($restAreasCount > 0) $roomSummaryParts[] = "{$restAreasCount} Relaxation & Gaming Lounge(s) with sofas, coffee table, and beanbag seating";
        if ($theatersCount > 0) $roomSummaryParts[] = "{$theatersCount} Presentation Theater / Auditorium with elevated stage, large glowing curved presentation screen, and neat rows of auditorium seating";

        $roomsText = implode(', ', $roomSummaryParts);

        $prompt = "{$promptPrefix} A complete, full-facility 2D architectural floor plan blueprint layout designed in {$styleConfig['description']}. The floorplan strictly contains the following distinct interior zones separated by clean cutaway walls and open doorways: {$roomsText}. Features polished concrete walkways, warm wood floor pods, soft circular overhead downlights, and potted green botanical plants in room corners. Crisp top-down 90-degree direct overhead bird's-eye architectural drawing, hyper-detailed furniture arrangement, completely empty with no people or human characters.";

        // 5. Generate Blueprint Artwork via OpenAI DALL-E 3
        if (empty($apiKey) || empty($aiSettings['is_enabled'])) {
            throw ValidationException::withMessages([
                'api_key' => __('OpenAI API key is missing or AI Generator is disabled in SuperAdmin settings. Please configure an active OpenAI API key in SuperAdmin Settings.'),
            ]);
        }

        $imageUrl = null;
        try {
            // Attempt with configured model or auto-fallback to gpt-image-1 / chatgpt-image-latest
            $attemptModels = array_unique([$model, 'gpt-image-1', 'chatgpt-image-latest', 'dall-e-3', 'dall-e-2']);
            $lastErrMsg = null;

            foreach ($attemptModels as $currentModel) {
                $payload = [
                    'model' => $currentModel,
                    'prompt' => $prompt,
                    'n' => 1,
                ];
                if (!empty($imageSize)) {
                    $payload['size'] = $imageSize;
                }

                $response = Http::withToken($apiKey)
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/images/generations', $payload);

                if ($response->successful()) {
                    $openAiData = $response->json();
                    $remoteImageUrl = $openAiData['data'][0]['url'] ?? null;
                    $b64Data = $openAiData['data'][0]['b64_json'] ?? null;

                    $filename = 'ai_map_' . $organization->id . '_' . time() . '.png';
                    $destinationDir = public_path('uploads/maps');
                    if (!File::isDirectory($destinationDir)) {
                        File::makeDirectory($destinationDir, 0777, true, true);
                    }

                    if (!empty($b64Data)) {
                        $imageBinary = base64_decode($b64Data);
                        File::put($destinationDir . '/' . $filename, $imageBinary);
                        $imageUrl = '/uploads/maps/' . $filename;
                        break;
                    } elseif (!empty($remoteImageUrl)) {
                        $imageBinary = Http::timeout(60)->get($remoteImageUrl)->body();
                        File::put($destinationDir . '/' . $filename, $imageBinary);
                        $imageUrl = '/uploads/maps/' . $filename;
                        break;
                    }
                } else {
                    $errBody = $response->json();
                    $lastErrMsg = $errBody['error']['message'] ?? $response->body();
                    Log::warning("OpenAI Model {$currentModel} failed: {$lastErrMsg}. Trying fallback...");
                }
            }

            if (!$imageUrl) {
                throw ValidationException::withMessages([
                    'openai' => __('OpenAI Image Generation Error: :error', ['error' => $lastErrMsg ?? 'Could not generate image.']),
                ]);
            }
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            Log::error('OpenAI Request Failed: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'openai' => __('Failed to communicate with OpenAI API: :error', ['error' => $e->getMessage()]),
            ]);
        }

        // 6. Update Target Floor and Map Background Artwork (Preserve existing manual rooms)
        $gridWidth = 48;
        $gridHeight = 32;
        $tileSize = 16;

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

        return [
            'success' => true,
            'floor' => $floor,
            'map' => $map->fresh(['rooms']),
            'rooms' => $map->rooms,
            'background_image_url' => $imageUrl,
            'message' => __('AI Workplace & Floorplan Blueprint generated successfully via OpenAI DALL-E 3!'),
        ];
    }

    /**
     * Get available architectural styles list.
     */
    public function getStyles(): array
    {
        return $this->styles;
    }
}

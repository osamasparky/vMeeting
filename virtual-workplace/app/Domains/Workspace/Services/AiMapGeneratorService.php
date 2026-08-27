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
     * @throws ValidationException
     */
    public function generate(Organization $organization, array $options, ?User $user = null): array
    {
        $plan = $organization->plan;

        // 1. Parse room quantities safely
        $meetingRoomsCount = isset($options['meeting_rooms']) ? max(0, min(6, (int) $options['meeting_rooms'])) : 0;
        $officeRoomsCount = isset($options['office_rooms']) ? max(1, min(8, (int) $options['office_rooms'])) : 1;
        $desksPerOffice = isset($options['desks_per_office']) ? max(1, min(12, (int) $options['desks_per_office'])) : 1;
        $thinkingRoomsCount = isset($options['thinking_rooms']) ? max(0, min(4, (int) $options['thinking_rooms'])) : 0;
        $restAreasCount = isset($options['rest_areas']) ? max(0, min(3, (int) $options['rest_areas'])) : 0;
        $theatersCount = isset($options['theaters']) ? max(0, min(2, (int) $options['theaters'])) : 0;
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
                'rooms' => __('Your current subscription plan (:plan) allows up to :limit rooms. You selected :total rooms. Please upgrade your plan or reduce room counts.', [
                    'plan' => $plan->name,
                    'limit' => $plan->room_limit,
                    'total' => $totalRooms,
                ]),
            ]);
        }

        if ($plan && $plan->seat_limit > 0 && $totalDesks > $plan->seat_limit) {
            throw ValidationException::withMessages([
                'seats' => __('Your current subscription plan (:plan) allows up to :limit members/seats. Your office has :total desks. Please reduce the number of desks or offices.', [
                    'plan' => $plan->name,
                    'limit' => $plan->seat_limit,
                    'total' => $totalDesks,
                ]),
            ]);
        }

        // 3. Obtain OpenAI Configuration (Prioritize Organization-level settings, fallback to Platform SuperAdmin)
        $orgOpenAi = $organization->settings?->openai_settings ?? [];
        $platformOpenAi = SystemSetting::get('openai_settings', []);

        $apiKey = ! empty($orgOpenAi['api_key']) ? trim($orgOpenAi['api_key']) : (! empty($platformOpenAi['api_key']) ? trim($platformOpenAi['api_key']) : env('OPENAI_API_KEY', ''));
        $model = ! empty($orgOpenAi['model']) ? $orgOpenAi['model'] : ($platformOpenAi['model'] ?? 'gpt-image-1-mini');
        $imageSize = ! empty($orgOpenAi['image_size']) ? $orgOpenAi['image_size'] : ($platformOpenAi['image_size'] ?? '1024x1024');
        $quality = ! empty($orgOpenAi['quality']) ? $orgOpenAi['quality'] : ($platformOpenAi['quality'] ?? 'standard');

        // 4. Construct Ultra-Compact & Token-Optimized 2D Architectural Blueprint Prompt (Massive Token & Cost Reduction)
        $zones = ['Reception', 'Espresso Coffee Bar'];
        if ($meetingRoomsCount > 0) {
            $zones[] = "{$meetingRoomsCount} Meeting Boardrooms";
        }
        if ($officeRoomsCount > 0) {
            $zones[] = "{$officeRoomsCount} Workspaces ({$desksPerOffice} workstations each)";
        }
        if ($thinkingRoomsCount > 0) {
            $zones[] = "{$thinkingRoomsCount} Thinking Pods";
        }
        if ($restAreasCount > 0) {
            $zones[] = "{$restAreasCount} Breakout Lounges";
        }
        if ($theatersCount > 0) {
            $zones[] = "{$theatersCount} Presentation Theaters";
        }
        $zonesText = implode(', ', $zones);

        $prompt = "Top-down 2D architectural blueprint floorplan, {$styleConfig['name']} office style. Empty modern workspace layout with: {$zonesText}. Overhead bird's-eye view, interior cutaway partition walls, open doorways, clean floor textures, desks, meeting tables, lounge seating, indoor potted plants, soft circular overhead lighting. Completely empty, no people.";

        // 5. Generate Blueprint Artwork via OpenAI DALL-E / GPT Image Model
        if (empty($apiKey)) {
            throw ValidationException::withMessages([
                'api_key' => __('OpenAI API key is missing. Please enter your organization OpenAI API key in Workspace Settings (⚙️ Settings -> OpenAI Settings).'),
            ]);
        }

        $imageUrl = null;
        try {
            // Attempt with configured model or auto-fallback to budget-efficient models
            $attemptModels = array_unique([$model, 'gpt-image-1-mini', 'gpt-image-1', 'chatgpt-image-latest', 'dall-e-3', 'dall-e-2']);
            $lastErrMsg = null;

            foreach ($attemptModels as $currentModel) {
                $payload = [
                    'model' => $currentModel,
                    'prompt' => $prompt,
                    'n' => 1,
                ];
                if (! empty($imageSize)) {
                    $payload['size'] = $imageSize;
                }

                $response = Http::withToken($apiKey)
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/images/generations', $payload);

                if ($response->successful()) {
                    $openAiData = $response->json();
                    $remoteImageUrl = $openAiData['data'][0]['url'] ?? null;
                    $b64Data = $openAiData['data'][0]['b64_json'] ?? null;

                    $filename = 'ai_map_'.$organization->id.'_'.time().'.png';
                    $destinationDir = \App\Services\FileUploadService::ensureDirectory(public_path('uploads/maps'), 0755);

                    if (! empty($b64Data)) {
                        $imageBinary = base64_decode($b64Data);
                        $filePath = $destinationDir.'/'.$filename;
                        File::put($filePath, $imageBinary);
                        $imageUrl = '/uploads/maps/'.$filename;
                        break;
                    } elseif (! empty($remoteImageUrl)) {
                        $imageBinary = Http::timeout(60)->get($remoteImageUrl)->body();
                        $filePath = $destinationDir.'/'.$filename;
                        File::put($filePath, $imageBinary);
                        $imageUrl = '/uploads/maps/'.$filename;
                        break;
                    }
                } else {
                    $errBody = $response->json();
                    $lastErrMsg = $errBody['error']['message'] ?? $response->body();
                    Log::warning("OpenAI Model {$currentModel} failed: {$lastErrMsg}. Trying fallback...");
                }
            }

            if (! $imageUrl) {
                throw ValidationException::withMessages([
                    'openai' => __('OpenAI Image Generation Error: :error', ['error' => $lastErrMsg ?? 'Could not generate image.']),
                ]);
            }
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            Log::error('OpenAI Request Failed: '.$e->getMessage());
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

        if (! $floor) {
            $floorName = ! empty($options['floor_name']) ? trim($options['floor_name']) : ($styleConfig['name'].' '.__('Office'));
            $isFirstFloor = ($organization->floors()->count() === 0);
            $floor = $organization->floors()->create([
                'name' => $floorName,
                'is_default' => $isFirstFloor,
                'order' => $organization->floors()->max('order') + 1,
            ]);
        }

        // Get or Create Map
        $map = $organization->maps()->where('floor_id', $floor->id)->first();
        if (! $map) {
            $map = $organization->maps()->create([
                'floor_id' => $floor->id,
                'name' => $floor->name.' AI Blueprint',
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

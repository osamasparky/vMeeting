<?php

namespace App\Domains\Workspace\Templates;

use App\Domains\Workspace\Models\FurnitureItem;

/**
 * Designs the plan office templates (rooms + furniture from the catalogue + a floor image).
 *
 * Coordinates are in 16px tiles. Catalogue pictures are drawn "front facing down", so a chair
 * at rotation 0 faces the bottom of the map; 180 faces up, 90 faces left, 270 faces right.
 * Layers: 0 rugs, 1 seats, 2 desks/tables/storage, 3 things standing on a desk.
 * Every piece is walk-through (not solid); owners can make pieces solid in the editor.
 */
class OfficeTemplateDesigner
{
    public const TILE = 16;

    /** Catalogue sizes look small next to an avatar; pieces are drawn 1.2x (rugs are sized per room). */
    private const SCALE = 1.2;

    private array $catalog = [];

    private array $objects = [];

    private array $rooms = [];

    private array $floors = [];

    /** @var list<string> catalogue slugs the last design asked for but the catalogue lacks */
    public array $missing = [];

    public function __construct()
    {
        $this->catalog = FurnitureItem::get(['slug', 'name', 'image_url', 'width', 'height', 'interaction_type', 'interaction_config'])
            ->keyBy('slug')->all();
    }

    /** @return array{width:int,height:int,rooms:array,objects:array,floors:array,name:string,description:string} */
    public function design(string $plan): array
    {
        $this->objects = $this->rooms = $this->floors = $this->missing = [];

        return $plan === 'business' ? $this->business() : $this->free();
    }

    // ── Plans ────────────────────────────────────────────────────────────

    private function free(): array
    {
        $this->meetingRoom('غرفة الاجتماعات - Meeting Room', 4, 4, 28, 18, 'bottom', 0.5, big: true);
        $this->office('المكتب - Office', 32, 4, 24, 18, 4, 'bottom', 0.5);
        $this->thinkingRoom('غرفة التفكير - Thinking Room', 60, 4, 11, 10, 'bottom', 0.5);
        $this->openArea(4, 26, 67, 16, compact: true);
        // The plan has three rooms, so the kitchen and print station sit in the open area.
        $this->kitchenette(49, 29);
        $this->printStation(66.5, 35.5);

        return $this->result('free', 75, 45,
            'المكتب الأساسي - Free Starter Office',
            'غرفة اجتماعات، مكتب لأربعة موظفين، وغرفة تفكير، مع استقبال وجلسة انتظار وركن مطبخ وطابعة في المساحة المفتوحة.');
    }

    private function business(): array
    {
        for ($i = 0; $i < 6; $i++) {
            $this->office(sprintf('مكتب صغير %d - Small Office %d', $i + 1, $i + 1), 4 + 22 * $i, 4, 22, 19, 4, 'bottom', 0.5);
        }
        for ($i = 0; $i < 3; $i++) {
            $this->office(sprintf('مكتب كبير %d - Large Office %d', $i + 1, $i + 1), 4 + 30 * $i, 30, 30, 19, 6, 'top', 0.88);
        }
        $this->ceoOffice('مكتب المدير التنفيذي - CEO Office', 98, 30, 30, 19);
        $this->meetingRoom('قاعة الاجتماعات الكبرى - Large Meeting Room', 4, 56, 30, 19, 'top', 0.85, big: true);
        $this->meetingRoom('قاعة الاجتماعات الصغرى - Small Meeting Room', 34, 56, 20, 15, 'top', 0.85, big: false);
        $this->coffeeRoom('ركن القهوة - Coffee Room', 58, 56, 26, 19);
        $this->library('المكتبة - Library', 84, 56, 24, 19);
        $this->relaxArea('منطقة الاسترخاء - Relax Area', 112, 56, 24, 19);
        for ($i = 0; $i < 4; $i++) {
            $this->thinkingRoom(sprintf('غرفة تفكير %d - Thinking Room %d', $i + 1, $i + 1), 96 + 10 * $i, 82, 10, 10, 'top', 0.5);
        }
        $this->openArea(4, 77, 88, 15, compact: false);
        $this->printStation(76, 79);
        // Greenery along the corridors and beside the CEO office.
        foreach ([[24, 25], [70, 25], [116, 25], [40, 51], [96, 51], [132, 32.5]] as [$x, $y]) {
            $this->at('planter_6', $x, $y, 0, 2);
        }
        $this->plant(132, 42);

        return $this->result('business', 140, 94,
            'مقر الأعمال - Business Headquarters',
            '6 مكاتب صغيرة (4 موظفين)، 3 مكاتب كبيرة (6 موظفين)، مكتب المدير التنفيذي، قاعتا اجتماعات، ركن قهوة، مكتبة، منطقة استرخاء، و4 غرف تفكير فردية.');
    }

    // ── Rooms ────────────────────────────────────────────────────────────

    private function room(string $name, string $type, int $capacity, int $x, int $y, int $w, int $h, string $doorSide, float $doorOffset, string $floor, string $access = 'public'): void
    {
        $this->rooms[] = [
            'name' => $name,
            'type' => $type,
            'access_mode' => $access,
            'capacity' => $capacity,
            'color' => '#3F7D4F',
            'bounds' => ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h, 'doorSide' => $doorSide, 'doorOffset' => $doorOffset],
            'metadata' => ['audio_isolation' => true],
        ];
        $this->floors[] = ['x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'texture' => $floor];
    }

    /** Desks with a chair behind each, in two rows; 4 or 6 people. */
    private function office(string $name, int $rx, int $ry, int $w, int $h, int $people, string $door, float $offset): void
    {
        $this->room($name, 'private', $people, $rx, $ry, $w, $h, $door, $offset, 'floor_light_birch');
        $cols = $people === 6 ? [5, 13.5, 22] : [$w / 2 - 5, $w / 2 + 5];
        $rows = $door === 'top' ? [9, 16] : [7.5, 15];
        foreach ($rows as $row) {
            foreach ($cols as $c) {
                $this->workstation($rx + $c, $ry + $row);
            }
        }
        // Storage and plants along the walls.
        $this->at('file_cabinet', $rx + $w - 2.4, $ry + ($door === 'top' ? $h - 2.6 : 2.8), 0, 2);
        $this->plant($rx + 1.9, $ry + ($door === 'top' ? $h - 2.6 : 2.6));
        $this->at('plant_shelf_1', $rx + $w - 1.6, $ry + $h / 2 + 0.5, 0, 2, 2.4, 4.8);
        if ($people === 6) {
            $this->at('printer', $rx + 2, $ry + 4, 0, 2);
        }
    }

    private function workstation(float $cx, float $cy): void
    {
        $this->at('chair_96', $cx, $cy - 3.5, 0, 1);
        $this->at('desk_10', $cx, $cy, 0, 2);
        // The person sits above the desk facing down, so the screen faces up at them.
        $this->at('monitor', $cx - 0.3, $cy - 0.1, 180, 3);
        $this->at('keyboard', $cx - 0.3, $cy - 1.8, 180, 3);
        $this->at('desk_plant', $cx + 2.1, $cy - 1, 0, 3, 1, 2);
    }

    /** A potted plant (the catalogue's desk plant, floor size). */
    private function plant(float $cx, float $cy): void
    {
        $this->at('desk_plant', $cx, $cy, 0, 2, 2.4, 4.8);
    }

    private function ceoOffice(string $name, int $rx, int $ry, int $w, int $h): void
    {
        $this->room($name, 'private', 4, $rx, $ry, $w, $h, 'top', 0.85, 'floor_dark_walnut', access: 'private');
        // Executive desk on a rug, the CEO behind it and two guest chairs in front.
        $this->at('large_rug_13', $rx + 9.5, $ry + 10.5, 0, 0, 15, 10);
        $this->at('chair_55', $rx + 9.5, $ry + 5.8, 0, 1);
        $this->at('desk_15', $rx + 9.5, $ry + 9.5, 0, 2, 8.4, 6.3);
        $this->at('monitor_2', $rx + 9.5, $ry + 10.6, 180, 3);
        $this->at('phone', $rx + 12.3, $ry + 8.6, 0, 3);
        $this->at('coat_rack', $rx + $w - 1.8, $ry + $h - 3.4, 0, 2);
        $this->at('chair_74', $rx + 7.3, $ry + 14.4, 180, 1);
        $this->at('chair_74', $rx + 11.7, $ry + 14.4, 180, 1);
        // Sitting corner.
        $this->at('large_rug_37', $rx + 22.5, $ry + 12.5, 0, 0, 12, 8);
        $this->at('sofa_39', $rx + 22.5, $ry + 16, 180, 1);
        $this->at('coffee_table_15', $rx + 22.5, $ry + 11.8, 0, 2);
        $this->at('chair_82', $rx + 17.4, $ry + 11.6, 270, 1);
        $this->at('chair_82', $rx + 27.6, $ry + 11.6, 90, 1);
        // Books, plants and a lamp.
        $this->at('bookcase_3', $rx + 23, $ry + 4.4, 0, 2);
        $this->plant($rx + 15.5, $ry + 3.4);
        $this->plant($rx + 2, $ry + $h - 2.6);
        $this->at('floor_lamp', $rx + 2.2, $ry + 4, 0, 2);
    }

    private function meetingRoom(string $name, int $rx, int $ry, int $w, int $h, string $door, float $offset, bool $big): void
    {
        $this->room($name, 'meeting', $big ? 10 : 6, $rx, $ry, $w, $h, $door, $offset, 'floor_executive_carpet');
        $cx = $rx + $w / 2 - 1;
        $cy = $ry + $h / 2 + ($door === 'top' ? 1 : -0.3);
        if ($big) {
            $this->at('large_rug_6', $cx, $cy, 0, 0, 22, 14);
            $this->at('conference_table_12', $cx, $cy, 0, 2);
            foreach ([-5.4, -1.8, 1.8, 5.4] as $dx) {
                $this->at('chair_72', $cx + $dx, $cy - 4.2, 0, 1);
                $this->at('chair_72', $cx + $dx, $cy + 4.2, 180, 1);
            }
            $this->at('chair_72', $cx - 8.7, $cy, 270, 1);
            $this->at('chair_72', $cx + 8.7, $cy, 90, 1);
            $this->at('whiteboard', $rx + $w - 3.2, $cy + ($door === 'top' ? 2.5 : 0), 0, 2, 5, 5);
        } else {
            $this->at('large_rug_6', $cx, $cy, 0, 0, 14, 9.5);
            $this->at('conference_table_12', $cx, $cy, 0, 2, 8.4, 3.5);
            foreach ([-2.6, 0, 2.6] as $dx) {
                $this->at('chair_72', $cx + $dx, $cy - 3.1, 0, 1);
                $this->at('chair_72', $cx + $dx, $cy + 3.1, 180, 1);
            }
            $this->at('whiteboard', $rx + $w - 2.6, $cy + 1.5, 0, 2, 4, 4);
        }
        $this->plant($rx + 2, $ry + ($door === 'top' ? $h - 2.6 : 2.6));
    }

    private function coffeeRoom(string $name, int $rx, int $ry, int $w, int $h): void
    {
        $this->room($name, 'breakout', 12, $rx, $ry, $w, $h, 'top', 0.85, 'floor_ceramic_beige');
        $this->at('bar_counter_2', $rx + 8.4, $ry + 4.3, 0, 2);
        $this->at('espresso_machine', $rx + 5, $ry + 2.4, 0, 3);
        $this->at('cabinets_1', $rx + 18, $ry + 3, 0, 2);
        $this->at('water_cooler', $rx + $w - 1.8, $ry + 9, 0, 2);
        // Island with stools.
        $this->at('kitchen_island', $rx + 13, $ry + 10.3, 0, 2);
        foreach ([-2.6, 0, 2.6] as $dx) {
            $this->at('stool_13', $rx + 13 + $dx, $ry + 13.6, 0, 1);
        }
        // Two small tables for two.
        foreach ([5, 21] as $tx) {
            $this->at('round_table_6', $rx + $tx, $ry + 16, 0, 2);
            $this->at('chair_74', $rx + $tx - 3.8, $ry + 16, 270, 1);
            $this->at('chair_74', $rx + $tx + 3.8, $ry + 16, 90, 1);
        }
        $this->plant($rx + 2, $ry + 10);
    }

    private function library(string $name, int $rx, int $ry, int $w, int $h): void
    {
        $this->room($name, 'focus', 8, $rx, $ry, $w, $h, 'top', 0.5, 'floor_chevron_timber');
        $this->at('bookcase_3', $rx + 5.5, $ry + 4.2, 0, 2);
        $this->at('bookcase_3', $rx + 18.5, $ry + 4.2, 0, 2);
        $this->at('bookcase_2', $rx + 2.8, $ry + 12.5, 0, 2);
        $this->at('bookcase_2', $rx + $w - 2.8, $ry + 12.5, 0, 2);
        // Reading corner around a small table.
        $this->at('large_rug_13', $rx + 12, $ry + 13, 0, 0, 13.5, 9);
        $this->at('coffee_table_8', $rx + 12, $ry + 12.8, 0, 2);
        $this->at('chair_82', $rx + 8, $ry + 12.6, 270, 1);
        $this->at('chair_82', $rx + 16, $ry + 12.6, 90, 1);
        $this->at('chair_82', $rx + 12, $ry + 16.4, 180, 1);
        $this->at('floor_lamp', $rx + 7.3, $ry + 16.3, 0, 2);
        $this->plant($rx + $w - 2.2, $ry + $h - 2.6);
    }

    private function relaxArea(string $name, int $rx, int $ry, int $w, int $h): void
    {
        $this->room($name, 'breakout', 10, $rx, $ry, $w, $h, 'top', 0.5, 'floor_japanese_tatami');
        $this->at('large_rug_27', $rx + 12, $ry + 11.5, 0, 0, 13, 13);
        $this->at('sofa_1', $rx + 12, $ry + 15.6, 180, 1);
        $this->at('coffee_table_15', $rx + 12, $ry + 10.8, 0, 2);
        $this->at('bean_bag_chair', $rx + 7.6, $ry + 7.6, 0, 1);
        $this->at('bean_bag_chair_2', $rx + 16.4, $ry + 7.6, 0, 1);
        $this->at('bean_bag_chair_6', $rx + 6.4, $ry + 12.4, 270, 1);
        $this->at('bean_bag_chair_6', $rx + 17.6, $ry + 12.4, 90, 1);
        $this->plant($rx + 2.4, $ry + 3.4);
        $this->plant($rx + $w - 2.4, $ry + 3.4);
        $this->at('floor_lamp', $rx + 2.2, $ry + 16, 0, 2);
        $this->plant($rx + $w - 2.2, $ry + $h - 2.6);
    }

    /** Open-plan kitchen corner: counter with sink, coffee and microwave, a fridge and a table for three. */
    private function kitchenette(float $x, float $y): void
    {
        $this->at('counter', $x + 4, $y + 1.6, 0, 2, 8, 3.4);
        $this->at('sink', $x + 1.6, $y + 1.4, 0, 3, 2.4, 2.6);
        $this->at('espresso_machine', $x + 4.6, $y + 1.3, 0, 3, 3, 1.5);
        $this->at('microwave', $x + 7, $y + 1.3, 0, 3);
        $this->at('refrigerator', $x + 10.6, $y + 2.6, 0, 2);
        $this->at('round_table_6', $x + 5, $y + 8, 0, 2);
        $this->at('chair_74', $x + 1.3, $y + 8, 270, 1);
        $this->at('chair_74', $x + 8.7, $y + 8, 90, 1);
        $this->at('chair_74', $x + 5, $y + 11.2, 180, 1);
    }

    /** Shared printer, filing and a coat rack. */
    private function printStation(float $x, float $y): void
    {
        $this->at('printer', $x, $y + 1.4, 0, 2);
        $this->at('file_cabinet_1', $x + 3.4, $y + 1.6, 0, 2);
        $this->at('coat_rack', $x - 3, $y + 2.2, 0, 2);
    }

    /** A single-person room: armchair, side table, lamp and a plant. */
    private function thinkingRoom(string $name, int $rx, int $ry, int $w, int $h, string $door, float $offset): void
    {
        $this->room($name, 'focus', 1, $rx, $ry, $w, $h, $door, $offset, 'floor_charcoal_carpet');
        $base = $door === 'top' ? $ry + 1.2 : $ry;
        $this->at('large_rug_37', $rx + $w / 2, $base + 5.6, 0, 0, 7, 4.6);
        $this->at('chair_144', $rx + $w / 2 - 1.3, $base + 5.2, 0, 1);
        $this->at('coffee_table_8', $rx + $w / 2 + 2.4, $base + 6.3, 0, 2);
        $this->at('floor_lamp', $rx + $w - 1.5, $base + 3.6, 0, 2, 2, 4);
        $this->at('desk_plant', $rx + 1.5, $base + ($door === 'top' ? 6.8 : 2.4), 0, 2, 1.8, 3.6);
    }

    /** Reception desk and a waiting corner in the open area. */
    private function openArea(int $x, int $y, int $w, int $h, bool $compact): void
    {
        $rx = $x + $w / 2;
        $ry = $y + 5;
        // Reception.
        $this->at('chair_96', $rx, $ry - 3.2, 0, 1);
        $this->at('counter', $rx, $ry, 0, 2, 10, 5);
        $this->plant($rx - 7, $ry - 0.5);
        $this->plant($rx + 7, $ry - 0.5);
        // Waiting corner.
        $wx = $x + ($compact ? 14 : 18);
        $wy = $y + $h - 5;
        $this->at('large_rug_33', $wx, $wy - 1, 0, 0, 16, 10);
        $this->at('sofa_24', $wx, $wy + 1.8, 180, 1);
        $this->at('coffee_table', $wx, $wy - 2, 0, 2);
        $this->at('chair_144', $wx - 6.6, $wy - 2, 270, 1);
        $this->at('chair_144', $wx + 6.6, $wy - 2, 90, 1);
        $this->at('water_cooler', $x + $w - 3, $y + 3, 0, 2);
        $this->plant($x + 2.4, $y + 3);
        if (! $compact) {
            $this->at('ping_pong_table', $x + $w - 20, $y + $h - 5.5, 0, 2);
        }
    }

    // ── Placement ────────────────────────────────────────────────────────

    /** Place a catalogue piece centred on (cx, cy); size defaults to the catalogue size. */
    private function at(string $slug, float $cx, float $cy, int $rotation = 0, int $layer = 2, ?float $w = null, ?float $h = null): void
    {
        $item = $this->catalog[$slug] ?? null;
        if (! $item || ! $item->image_url) {
            $this->missing[$slug] = $slug;

            return;
        }
        $w ??= round($item->width * self::SCALE, 2);
        $h ??= round($item->height * self::SCALE, 2);
        $x = round($cx - $w / 2, 2);
        $y = round($cy - $h / 2, 2);
        $interaction = $item->interaction_type ?: 'none';
        $config = is_array($item->interaction_config) ? $item->interaction_config : [];

        $this->objects[] = [
            'type' => $slug,
            'name' => $item->name,
            'position' => ['x' => $x, 'y' => $y, 'rotation' => $rotation],
            'width' => $w,
            'height' => $h,
            'rotation' => $rotation,
            'image_url' => $item->image_url,
            'is_custom' => false,
            'collision' => false,
            'elevation' => $layer,
            'interaction_type' => $interaction,
            'interaction_config' => array_merge($config, ['image_url' => $item->image_url, 'elevation' => $layer, 'interaction_type' => $interaction]),
        ];
    }

    private function result(string $plan, int $w, int $h, string $name, string $description): array
    {
        return [
            'plan' => $plan,
            'name' => $name,
            'description' => $description,
            'width' => $w,
            'height' => $h,
            'rooms' => $this->rooms,
            'objects' => $this->objects,
            'floors' => $this->floors,
        ];
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leftovers of the furniture catalogue's original product:
 *  - its catalogue search tag "kumogames" becomes "ulagames";
 *  - the Whiteboard was imported with that product's eraser.io
 * integration links (embedUrl / previewUrl). Nothing reads them — our
 * whiteboard is built in — so they are removed wherever the whiteboard's
 * settings were copied: the catalogue, placed objects, office templates and
 * saved map versions. Only those two fields, and only when they point at
 * eraser.io, are dropped; everything else is left exactly as it is.
 */
return new class extends Migration
{
    private const HOST = 'https://app.eraser.io/';

    private const KEYS = ['embedUrl', 'previewUrl'];

    private const TAGS = ['kumogames' => 'ulagames'];

    private const COLUMNS = [
        ['furniture_items', 'interaction_config'],
        ['map_objects', 'interaction_config'],
        ['office_templates', 'objects_data'],
        ['map_versions', 'layout_snapshot'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }
            $key = Schema::hasColumn($table, 'id') ? 'id' : null;
            if (! $key) {
                continue;
            }
            DB::table($table)->where(fn ($q) => $q->where($column, 'like', '%app.eraser.io%')->orWhere($column, 'like', '%kumogames%'))->orderBy($key)
                ->each(function ($row) use ($table, $column, $key) {
                    $data = json_decode((string) $row->{$column}, true);
                    if (! is_array($data)) {
                        return;
                    }
                    $clean = $this->strip($data);
                    if ($clean !== $data) {
                        DB::table($table)->where($key, $row->{$key})->update([$column => json_encode($clean)]);
                    }
                });
        }
    }

    public function down(): void
    {
        // The removed links were unused; there is nothing to restore.
    }

    private function strip(array $data): array
    {
        foreach ($data as $k => $v) {
            if (in_array($k, self::KEYS, true) && is_string($v) && str_starts_with($v, self::HOST)) {
                unset($data[$k]); // only ever a named key, so lists keep their shape
            } elseif ($k === 'tags' && is_array($v)) {
                $data[$k] = array_map(fn ($t) => self::TAGS[$t] ?? $t, $v);
            } elseif (is_array($v)) {
                $data[$k] = $this->strip($v);
            }
        }

        return $data;
    }
};

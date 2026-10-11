<?php

namespace App\Domains\Workspace\Templates;

/**
 * Draws a template's floor image: the building on polished concrete, each room on its own floor
 * texture (from public/images/floors), and a solid outer wall. Room walls and doors are drawn by
 * the office itself, so they always match the room bounds.
 */
class TemplateFloorRenderer
{
    private const TEXTURE_TILE_PX = 640;

    /** @var array<string, \GdImage> */
    private array $textures = [];

    /** @return string public URL of the written JPEG */
    public function render(array $design, string $file): string
    {
        $t = OfficeTemplateDesigner::TILE;
        $w = $design['width'] * $t;
        $h = $design['height'] * $t;
        $img = imagecreatetruecolor($w, $h);

        // Outside the building.
        imagefilledrectangle($img, 0, 0, $w, $h, imagecolorallocate($img, 226, 216, 199));

        // Building floor and outer wall.
        $bx = 2 * $t;
        $by = 2 * $t;
        $bw = $w - 4 * $t;
        $bh = $h - 4 * $t;
        $this->fill($img, 'floor_industrial_concrete', $bx, $by, $bw, $bh);
        $shade = imagecolorallocatealpha($img, 255, 255, 255, 96);
        imagefilledrectangle($img, $bx, $by, $bx + $bw, $by + $bh, $shade);

        foreach ($design['floors'] as $f) {
            $this->fill($img, $f['texture'], $f['x'] * $t, $f['y'] * $t, $f['w'] * $t, $f['h'] * $t);
            // A soft edge so rooms read as rooms even before the office draws their walls.
            $edge = imagecolorallocatealpha($img, 40, 30, 20, 100);
            imagesetthickness($img, 4);
            imagerectangle($img, $f['x'] * $t + 2, $f['y'] * $t + 2, ($f['x'] + $f['w']) * $t - 2, ($f['y'] + $f['h']) * $t - 2, $edge);
        }

        $wall = imagecolorallocate($img, 78, 63, 48);
        imagesetthickness($img, 10);
        imagerectangle($img, $bx - 5, $by - 5, $bx + $bw + 5, $by + $bh + 5, $wall);

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        imagejpeg($img, $file, 84);
        imagedestroy($img);

        return '/'.ltrim(str_replace('\\', '/', substr($file, strlen(public_path()))), '/');
    }

    private function fill(\GdImage $img, string $texture, int $x, int $y, int $w, int $h): void
    {
        $tile = $this->texture($texture);
        $tw = imagesx($tile);
        $th = imagesy($tile);
        for ($ty = $y; $ty < $y + $h; $ty += $th) {
            for ($tx = $x; $tx < $x + $w; $tx += $tw) {
                imagecopy($img, $tile, $tx, $ty, 0, 0, min($tw, $x + $w - $tx), min($th, $y + $h - $ty));
            }
        }
    }

    private function texture(string $name): \GdImage
    {
        if (isset($this->textures[$name])) {
            return $this->textures[$name];
        }
        $src = imagecreatefromjpeg(public_path("images/floors/{$name}.jpg"));
        $sw = imagesx($src);
        $sh = imagesy($src);
        $tw = self::TEXTURE_TILE_PX;
        $th = (int) round($sh * $tw / $sw);
        $tile = imagecreatetruecolor($tw, $th);
        imagecopyresampled($tile, $src, 0, 0, 0, 0, $tw, $th, $sw, $sh);
        imagedestroy($src);

        return $this->textures[$name] = $tile;
    }
}

<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Ensure a target directory exists with secure permissions (0755).
     *
     * @param string $path Absolute or relative filesystem directory path
     * @param int $permissions Default 0755 (never 0777)
     * @return string
     */
    public static function ensureDirectory(string $path, int $permissions = 0755): string
    {
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, $permissions, true, true);
        }

        return $path;
    }

    /**
     * Securely store an uploaded file with sanitized name and validated extension.
     *
     * @param UploadedFile $file
     * @param string $disk Storage disk ('public', 'local', etc.)
     * @param string $folder Relative destination folder (e.g. 'project_files/123')
     * @param array $allowedExtensions Optional whitelist of allowed file extensions
     * @return array Metadata about the uploaded file
     */
    public static function store(
        UploadedFile $file,
        string $folder = 'uploads',
        string $disk = 'public',
        array $allowedExtensions = []
    ): array {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! empty($allowedExtensions) && ! in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException(
                __('Invalid file extension ":ext". Allowed: :allowed', [
                    'ext' => $extension,
                    'allowed' => implode(', ', $allowedExtensions),
                ])
            );
        }

        // Sanitize original file name
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName);
        $filename = $safeName.'-'.Str::random(8).'.'.$extension;

        // Ensure target folder exists on public path if using local/public storage
        $destinationPath = Storage::disk($disk)->path($folder);
        self::ensureDirectory($destinationPath, 0755);

        // Store file
        $storedPath = $file->storeAs($folder, $filename, $disk);

        // Set safe file permissions (0644)
        $fullFilePath = Storage::disk($disk)->path($storedPath);
        if (File::exists($fullFilePath)) {
            @chmod($fullFilePath, 0644);
        }

        return [
            'disk' => $disk,
            'path' => $storedPath,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size_bytes' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'url' => Storage::disk($disk)->url($storedPath),
        ];
    }

    /**
     * Safely delete a file from storage.
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    public static function delete(?string $path, string $disk = 'public'): bool
    {
        if (empty($path)) {
            return false;
        }

        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable $e) {
            Log::warning('FileUploadService::delete failed: '.$e->getMessage(), ['path' => $path]);
        }

        return false;
    }
}

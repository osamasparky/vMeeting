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
     * Extensions that must never be accepted into a public upload directory,
     * regardless of the caller's own allowlist: server-executable types and
     * markup types capable of carrying an embedded script (HTML/SVG XSS).
     * Mirrors the check already applied to furniture image uploads in
     * SuperAdminController, generalized so any upload path can reuse it.
     */
    public const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'pht', 'phar',
        'exe', 'sh', 'bat', 'cmd', 'com', 'cgi', 'pl', 'py', 'rb',
        'jar', 'msi', 'dll', 'scr', 'vbs', 'ps1',
        'html', 'htm', 'js', 'mjs', 'xhtml',
    ];

    /**
     * Ensure a target directory exists with secure permissions (0755).
     *
     * @param  string  $path  Absolute or relative filesystem directory path
     * @param  int  $permissions  Default 0755 (never 0777)
     */
    public static function ensureDirectory(string $path, int $permissions = 0755): string
    {
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, $permissions, true, true);
        }

        return $path;
    }

    /**
     * Write an .htaccess to a public upload directory denying script
     * execution, if one isn't already present. Safe to call repeatedly.
     */
    public static function protectDirectoryFromExecution(string $path): void
    {
        $marker = rtrim($path, '/\\').DIRECTORY_SEPARATOR.'.htaccess';
        if (! File::exists($marker)) {
            @file_put_contents(
                $marker,
                "<Files *.php>\n    Order Deny,Allow\n    Deny from all\n</Files>\nOptions -ExecCGI\n"
            );
        }
    }

    /**
     * Reject a file that is dangerous to serve from a public upload
     * directory: a server-executable extension, or (for SVG specifically)
     * markup containing an embedded script/event-handler. Returns an error
     * string to show the user, or null if the file is safe to accept.
     */
    public static function rejectionReasonFor(UploadedFile $file, string $extension): ?string
    {
        if (in_array($extension, self::DANGEROUS_EXTENSIONS, true)) {
            return __('For security, files with the ":ext" extension cannot be uploaded.', ['ext' => $extension]);
        }

        if ($extension === 'svg') {
            $content = @file_get_contents($file->getRealPath()) ?: '';
            if (preg_match('/<script|javascript:|onload=|onerror=|onclick=|<foreignObject/i', $content)) {
                return __('This SVG file contains unsafe embedded scripts or attributes.');
            }
        }

        return null;
    }

    /**
     * Move an uploaded file into a protected public uploads directory with a
     * randomized, collision-resistant filename. Used by upload endpoints
     * that serve files as plain static links from the public webroot
     * (project/task attachments) rather than through Storage::disk().
     *
     * @return array{filename: string, path: string, size: int}
     */
    public static function moveToPublicUploads(UploadedFile $file, string $destDir, string $filenamePrefix, string $extension): array
    {
        self::ensureDirectory($destDir, 0755);
        self::protectDirectoryFromExecution($destDir);

        $filename = $filenamePrefix.'_'.time().'_'.Str::random(6).'.'.$extension;
        $file->move($destDir, $filename);

        $fullPath = rtrim($destDir, '/\\').'/'.$filename;

        return [
            'filename' => $filename,
            'path' => $fullPath,
            'size' => file_exists($fullPath) ? filesize($fullPath) : $file->getSize(),
        ];
    }

    /**
     * Securely store an uploaded file with sanitized name and validated extension.
     *
     * @param  string  $disk  Storage disk ('public', 'local', etc.)
     * @param  string  $folder  Relative destination folder (e.g. 'project_files/123')
     * @param  array  $allowedExtensions  Optional whitelist of allowed file extensions
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

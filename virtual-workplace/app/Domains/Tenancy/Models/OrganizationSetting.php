<?php

namespace App\Domains\Tenancy\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class OrganizationSetting extends Model
{
    protected $primaryKey = 'organization_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'organization_id',
        'branding',
        'policies',
        'smtp_settings',
        'openai_settings',
    ];

    protected $casts = [
        'branding' => 'array',
        'policies' => 'array',
    ];

    /**
     * Encrypted storage with backward-compatible decryption for OpenAI settings.
     */
    protected function openaiSettings(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return [];
                }
                try {
                    $decrypted = Crypt::decryptString($value);
                    $decoded = json_decode($decrypted, true);

                    return is_array($decoded) ? $decoded : [];
                } catch (DecryptException $e) {
                    // Graceful fallback for legacy plaintext JSON rows
                    $decoded = json_decode($value, true);

                    return is_array($decoded) ? $decoded : [];
                }
            },
            set: function ($value) {
                if (empty($value)) {
                    return null;
                }
                $json = is_array($value) ? json_encode($value) : (string) $value;

                return Crypt::encryptString($json);
            }
        );
    }

    /**
     * Encrypted storage with backward-compatible decryption for SMTP email settings.
     */
    protected function smtpSettings(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return [];
                }
                try {
                    $decrypted = Crypt::decryptString($value);
                    $decoded = json_decode($decrypted, true);

                    return is_array($decoded) ? $decoded : [];
                } catch (DecryptException $e) {
                    // Graceful fallback for legacy plaintext JSON rows
                    $decoded = json_decode($value, true);

                    return is_array($decoded) ? $decoded : [];
                }
            },
            set: function ($value) {
                if (empty($value)) {
                    return null;
                }
                $json = is_array($value) ? json_encode($value) : (string) $value;

                return Crypt::encryptString($json);
            }
        );
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get attendance and idle tracking policies.
     */
    public function getAttendancePolicy(): array
    {
        $default = [
            'auto_attendance_enabled' => true,
            'idle_prompt_minutes' => 15,
            'idle_response_grace_seconds' => 180,
            'allow_in_office_task_tracking' => true,
        ];

        return array_merge($default, $this->policies['attendance'] ?? []);
    }
}

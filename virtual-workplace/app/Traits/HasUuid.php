<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait HasUuid
 *
 * Auto-generates UUID for models that use UUID primary keys.
 * Must be used on models with a `uuid` type primary key column.
 */
trait HasUuid
{
    /**
     * Boot the HasUuid trait.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Indicates that the IDs are not auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * The type of the primary key ID.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}

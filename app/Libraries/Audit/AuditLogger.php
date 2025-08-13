<?php

namespace App\Libraries\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    protected static array $excludedAttributes = [
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'password',
        'password_confirmation',
    ];

    protected static ?string $customMessage = null;
    protected static array $additionalData = [];

    public static function created(Model $model, ?string $message = null): void
    {
        static::log($model, 'created', [], $model->getAttributes(), $message);
    }

    public static function updated(Model $model, ?string $message = null): void
    {
        $originalData = $model->getOriginal();
        $newData = $model->getAttributes();

        $changes = static::getChanges($originalData, $newData);

        if (!empty($changes['old']) || !empty($changes['new'])) {
            static::log($model, 'updated', $changes['old'], $changes['new'], $message);
        }
    }

    public static function deleted(Model $model, ?string $message = null): void
    {
        static::log($model, 'deleted', $model->getOriginal(), [], $message);
    }

    public static function attached(Model $model, string $relation, $attachedIds, array $attributes = [], ?string $message = null): void
    {
        $attachedIds = is_array($attachedIds) ? $attachedIds : [$attachedIds];

        static::log($model, 'attached', [], [
            'relation' => $relation,
            'attached_ids' => $attachedIds,
            'pivot_attributes' => $attributes,
        ], $message);
    }

    public static function detached(Model $model, string $relation, $detachedIds, ?string $message = null): void
    {
        $detachedIds = is_array($detachedIds) ? $detachedIds : [$detachedIds];

        static::log($model, 'detached', [
            'relation' => $relation,
            'detached_ids' => $detachedIds,
        ], [], $message);
    }

    public static function synced(Model $model, string $relation, array $changes, ?string $message = null): void
    {
        if (!empty($changes['attached']) || !empty($changes['detached']) || !empty($changes['updated'])) {
            static::log($model, 'synced', [], [
                'relation' => $relation,
                'changes' => $changes,
            ], $message);
        }
    }

    public static function custom(Model $model, string $event, array $oldValues = [], array $newValues = [], ?string $message = null): void
    {
        static::log($model, $event, $oldValues, $newValues, $message);
    }

    public static function withMessage(string $message): self
    {
        static::$customMessage = $message;
        return new static();
    }

    public static function withAdditionalData(array $data): self
    {
        static::$additionalData = array_merge(static::$additionalData, $data);
        return new static();
    }

    protected static function log(Model $model, string $event, array $oldValues, array $newValues, ?string $message = null): void
    {
        // Use custom message if provided, otherwise use the global custom message
        $finalMessage = $message ?? static::$customMessage;

        // Get excluded attributes - check if model has the method first
        $excludedAttributes = static::getExcludedAttributesForModel($model);

        // Filter out excluded attributes
        $oldValues = static::filterAttributes($oldValues, $excludedAttributes);
        $newValues = static::filterAttributes($newValues, $excludedAttributes);

        // Merge additional data
        $additionalData = static::$additionalData;

        AuditLog::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'message' => $finalMessage,
            'user_id' => static::getCurrentUserId(),
            'ip_address' => static::getClientIpAddress(),
            'user_agent' => static::getUserAgent(),
            'url' => static::getCurrentUrl(),
            'additional_data' => !empty($additionalData) ? $additionalData : null,
        ]);

        // Clear static properties after use
        static::$customMessage = null;
        static::$additionalData = [];
    }

    /**
     * Get excluded attributes for a specific model
     */
    protected static function getExcludedAttributesForModel(Model $model): array
    {
        // Check if model uses Auditable trait and has the method
        if (method_exists($model, 'getAuditExcluded')) {
            return $model->getAuditExcluded();
        }

        // Check if model has auditExcluded property
        if (property_exists($model, 'auditExcluded')) {
            return array_merge(static::$excludedAttributes, $model->auditExcluded);
        }

        // Fall back to default excluded attributes
        return static::$excludedAttributes;
    }

    protected static function getChanges(array $original, array $current): array
    {
        $oldValues = [];
        $newValues = [];

        foreach ($current as $key => $value) {
            if (array_key_exists($key, $original) && $original[$key] !== $value) {
                $oldValues[$key] = $original[$key];
                $newValues[$key] = $value;
            }
        }

        return ['old' => $oldValues, 'new' => $newValues];
    }

    protected static function filterAttributes(array $attributes, ?array $excludedAttributes = null): array
    {
        $excluded = $excludedAttributes ?? static::$excludedAttributes;
        return array_diff_key($attributes, array_flip($excluded));
    }

    protected static function getCurrentUserId(): ?int
    {
        return Auth::id();
    }

    protected static function getClientIpAddress(): ?string
    {
        return Request::ip();
    }

    protected static function getUserAgent(): ?string
    {
        return Request::userAgent();
    }

    protected static function getCurrentUrl(): ?string
    {
        return Request::fullUrl();
    }

    public static function setExcludedAttributes(array $attributes): void
    {
        static::$excludedAttributes = $attributes;
    }

    public static function addExcludedAttributes(array $attributes): void
    {
        static::$excludedAttributes = array_merge(static::$excludedAttributes, $attributes);
    }
}

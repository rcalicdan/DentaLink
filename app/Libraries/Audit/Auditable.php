<?php

namespace App\Libraries\Audit;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait Auditable
{
    protected ?string $auditMessage = null;
    protected static bool $auditingEnabled = true;
    protected static array $auditingDisabledFor = [];

    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            if ($model->shouldAudit()) {
                AuditLogger::created($model, $model->getAuditMessage());
            }
        });

        static::updated(function ($model) {
            if ($model->shouldAudit()) {
                AuditLogger::updated($model, $model->getAuditMessage());
            }
        });

        static::deleted(function ($model) {
            if ($model->shouldAudit()) {
                AuditLogger::deleted($model, $model->getAuditMessage());
            }
        });
    }

    /**
     * Override attach method to log many-to-many attachments
     */
    public function attach($id, array $attributes = [], $touch = true)
    {
        $relation = $this->getRelationFromBacktrace();

        if ($relation && $this->shouldAudit()) {
            $result = parent::attach($id, $attributes, $touch);
            AuditLogger::attached($this, $relation, $id, $attributes, $this->getAuditMessage());
            return $result;
        }

        return parent::attach($id, $attributes, $touch);
    }

    /**
     * Override detach method to log many-to-many detachments
     */
    public function detach($ids = null, $touch = true)
    {
        $relation = $this->getRelationFromBacktrace();

        if ($relation && $this->shouldAudit()) {
            $result = parent::detach($ids, $touch);
            AuditLogger::detached($this, $relation, $ids, $this->getAuditMessage());
            return $result;
        }

        return parent::detach($ids, $touch);
    }

    /**
     * Override sync method to log many-to-many sync operations
     */
    public function sync($ids, $detaching = true)
    {
        $relation = $this->getRelationFromBacktrace();

        if ($relation && $this->shouldAudit()) {
            $changes = parent::sync($ids, $detaching);
            AuditLogger::synced($this, $relation, $changes, $this->getAuditMessage());
            return $changes;
        }

        return parent::sync($ids, $detaching);
    }

    /**
     * Set a custom audit message for the next operation
     */
    public function setAuditMessage(string $message): self
    {
        $this->auditMessage = $message;
        return $this;
    }

    /**
     * Get the current audit message
     */
    public function getAuditMessage(): ?string
    {
        $message = $this->auditMessage;
        $this->auditMessage = null; // Clear after getting
        return $message;
    }

    /**
     * Perform an operation with a custom audit message
     */
    public function withAuditMessage(string $message, \Closure $callback)
    {
        $this->setAuditMessage($message);
        return $callback();
    }

    /**
     * Log a custom audit event
     */
    public function auditCustom(string $event, array $oldValues = [], array $newValues = [], ?string $message = null): void
    {
        if ($this->shouldAudit()) {
            AuditLogger::custom($this, $event, $oldValues, $newValues, $message ?? $this->getAuditMessage());
        }
    }

    /**
     * Temporarily disable auditing for this model instance
     */
    public function disableAuditing(): self
    {
        $this->auditEnabled = false;
        return $this;
    }

    /**
     * Re-enable auditing for this model instance
     */
    public function enableAuditing(): self
    {
        $this->auditEnabled = true;
        return $this;
    }

    /**
     * Temporarily disable auditing globally for all models
     */
    public static function disableAuditingGlobally(): void
    {
        static::$auditingEnabled = false;
    }

    /**
     * Re-enable auditing globally for all models
     */
    public static function enableAuditingGlobally(): void
    {
        static::$auditingEnabled = true;
    }

    /**
     * Disable auditing for specific model class
     */
    public static function disableAuditingFor(string $modelClass): void
    {
        static::$auditingDisabledFor[] = $modelClass;
    }

    /**
     * Enable auditing for specific model class
     */
    public static function enableAuditingFor(string $modelClass): void
    {
        static::$auditingDisabledFor = array_filter(
            static::$auditingDisabledFor,
            fn($class) => $class !== $modelClass
        );
    }

    /**
     * Execute callback without auditing for this instance
     */
    public function withoutAuditing(\Closure $callback)
    {
        $originalState = $this->getAuditingState();
        $this->disableAuditing();

        try {
            return $callback();
        } finally {
            $this->setAuditingState($originalState);
        }
    }

    /**
     * Execute callback without auditing globally
     */
    public static function withoutAuditingGlobally(\Closure $callback)
    {
        $wasEnabled = static::$auditingEnabled;
        static::disableAuditingGlobally();

        try {
            return $callback();
        } finally {
            if ($wasEnabled) {
                static::enableAuditingGlobally();
            }
        }
    }

    /**
     * Execute callback without auditing for specific model class
     */
    public static function withoutAuditingFor(string $modelClass, \Closure $callback)
    {
        $wasDisabled = in_array($modelClass, static::$auditingDisabledFor);

        if (!$wasDisabled) {
            static::disableAuditingFor($modelClass);
        }

        try {
            return $callback();
        } finally {
            if (!$wasDisabled) {
                static::enableAuditingFor($modelClass);
            }
        }
    }

    /**
     * Check if model should be audited
     */
    public function shouldAudit(): bool
    {
        // Check global auditing state
        if (!static::$auditingEnabled) {
            return false;
        }

        // Check if auditing is disabled for this model class
        if (in_array(static::class, static::$auditingDisabledFor)) {
            return false;
        }

        // Check instance-level setting
        if (property_exists($this, 'auditEnabled') && !$this->auditEnabled) {
            return false;
        }

        // Check if model has auditing disabled via property
        if (property_exists($this, 'disableAuditing') && $this->disableAuditing) {
            return false;
        }

        return true;
    }

    /**
     * Get the relation name from backtrace
     */
    protected function getRelationFromBacktrace(): ?string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        foreach ($trace as $frame) {
            if (isset($frame['object']) && $frame['object'] instanceof BelongsToMany) {
                return $frame['function'];
            }
        }

        return null;
    }

    /**
     * Get current auditing state
     */
    protected function getAuditingState(): bool
    {
        return property_exists($this, 'auditEnabled') ? $this->auditEnabled : true;
    }

    /**
     * Set auditing state
     */
    protected function setAuditingState(bool $state): void
    {
        $this->auditEnabled = $state;
    }

    /**
     * Get model-specific excluded attributes
     */
    public function getAuditExcluded(): array
    {
        $default = [
            'created_at',
            'updated_at',
            'deleted_at',
            'remember_token',
            'password',
            'password_confirmation'
        ];

        if (property_exists($this, 'auditExcluded')) {
            return array_merge($default, $this->auditExcluded);
        }

        return $default;
    }

    /**
     * Check if an attribute should be excluded from auditing
     */
    protected function shouldExcludeFromAudit(string $attribute): bool
    {
        return in_array($attribute, $this->getAuditExcluded());
    }

    /**
     * Get audit events that occurred for this model
     */
    public function auditLogs()
    {
        return $this->morphMany(\App\Models\AuditLog::class, 'auditable');
    }

    /**
     * Get the latest audit log entry
     */
    public function latestAuditLog()
    {
        return $this->auditLogs()->latest()->first();
    }

    /**
     * Get audit logs by event type
     */
    public function auditLogsByEvent(string $event)
    {
        return $this->auditLogs()->where('event', $event)->get();
    }

    /**
     * Check if model has been audited
     */
    public function hasBeenAudited(): bool
    {
        return $this->auditLogs()->exists();
    }

    /**
     * Get all unique events that have been audited for this model
     */
    public function getAuditedEvents(): array
    {
        return $this->auditLogs()
            ->select('event')
            ->distinct()
            ->pluck('event')
            ->toArray();
    }
}

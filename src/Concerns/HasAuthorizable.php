<?php

namespace NyonCode\LivewireTable\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasAuthorizable
 *
 * Provides two-level authorization for table components:
 * 1. Visibility - can the user see the component at all?
 * 2. Action/Edit - can the user perform the action or edit?
 *
 * @package NyonCode\LivewireTable\Concerns
 */
trait HasAuthorizable
{
    /**
     * Authorization callback for actions/edits.
     * Controls whether user can perform the action or edit the field.
     */
    protected ?Closure $authorizeCallback = null;

    /**
     * Visibility authorization callback.
     * Controls whether user can see the component at all.
     */
    protected ?Closure $visibilityCallback = null;

    /**
     * Whether to hide the component when action authorization fails.
     */
    protected bool $hideWhenUnauthorized = false;

    /**
     * Set the action/edit authorization callback.
     * This controls whether the user can perform an action or edit a field.
     *
     * Example:
     * ->authorize(fn($record) => Gate::allows('update', $record))
     * ->authorize(fn($record) => auth()->user()->can('edit users'))
     * ->authorize(fn($record) => $record->user_id === auth()->id())
     *
     * @param Closure $callback Function that receives the record and returns bool
     * @return $this
     */
    public function authorize(Closure $callback): static
    {
        $this->authorizeCallback = $callback;
        return $this;
    }

    /**
     * Set the visibility authorization callback.
     * This controls whether the user can see the component at all.
     * If this returns false, the component won't be rendered.
     *
     * Example:
     * ->visible(fn($record) => Gate::allows('view', $record))
     * ->visible(fn($record) => auth()->user()->can('view sensitive data'))
     * ->visible(fn($record) => !$record->is_confidential || auth()->user()->isAdmin())
     *
     * @param Closure $callback Function that receives the record and returns bool
     * @return $this
     */
    public function visible(Closure $callback): static
    {
        $this->visibilityCallback = $callback;
        return $this;
    }

    /**
     * Alias for visible() method for better readability in some contexts.
     *
     * @param Closure $callback
     * @return $this
     */
    public function authorizeView(Closure $callback): static
    {
        return $this->visible($callback);
    }

    /**
     * Hide the component when action authorization fails.
     * If false, component will be shown but disabled (default).
     *
     * @param bool $hide
     * @return $this
     */
    public function hideWhenUnauthorized(bool $hide = true): static
    {
        $this->hideWhenUnauthorized = $hide;
        return $this;
    }

    /**
     * Check if the current user is authorized to perform action/edit.
     *
     * @param Model|null $record The record to check authorization against
     * @return bool
     */
    public function isAuthorized(?Model $record = null): bool
    {
        // No authorization callback means everything is allowed
        if ($this->authorizeCallback === null) {
            return true;
        }

        // If no record provided, we can't check authorization
        if ($record === null) {
            return true;
        }

        return $this->executeCallback($this->authorizeCallback, $record, 'Action authorization');
    }

    /**
     * Check if the component is visible to the current user.
     *
     * @param Model|null $record The record to check visibility against
     * @return bool
     */
    public function isVisible(?Model $record = null): bool
    {
        // No visibility callback means everything is visible
        if ($this->visibilityCallback === null) {
            return true;
        }

        // If no record provided, we can't check visibility
        if ($record === null) {
            return true;
        }

        return $this->executeCallback($this->visibilityCallback, $record, 'Visibility check');
    }

    /**
     * Check if the component should be completely hidden.
     * Returns true if either:
     * 1. Visibility authorization fails, OR
     * 2. Action authorization fails AND hideWhenUnauthorized is true
     *
     * @param Model|null $record
     * @return bool
     */
    public function shouldBeHidden(?Model $record = null): bool
    {
        // First check visibility - if not visible, always hide
        if (!$this->isVisible($record)) {
            return true;
        }

        // Then check if should hide when action unauthorized
        if ($this->hideWhenUnauthorized && !$this->isAuthorized($record)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the component should be shown but disabled.
     * Returns true if:
     * - Component is visible BUT
     * - Action is not authorized AND
     * - hideWhenUnauthorized is false
     *
     * @param Model|null $record
     * @return bool
     */
    public function shouldBeDisabled(?Model $record = null): bool
    {
        // Must be visible first
        if (!$this->isVisible($record)) {
            return false;
        }

        // If not hiding when unauthorized, show as disabled
        return !$this->hideWhenUnauthorized && !$this->isAuthorized($record);
    }

    /**
     * Execute authorization callback with error handling.
     *
     * @param Closure $callback
     * @param Model $record
     * @param string $context
     * @return bool
     */
    protected function executeCallback(Closure $callback, Model $record, string $context): bool
    {
        try {
            return (bool) $callback($record);
        } catch (\Throwable $e) {
            // Log the error and deny access by default
            logger()->warning("$context failed", [
                'component' => static::class,
                'record_id' => $record->id ?? null,
                'record_type' => get_class($record),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Get the action/edit authorization callback.
     *
     * @return Closure|null
     */
    public function getAuthorizeCallback(): ?Closure
    {
        return $this->authorizeCallback;
    }

    /**
     * Get the visibility authorization callback.
     *
     * @return Closure|null
     */
    public function getVisibilityCallback(): ?Closure
    {
        return $this->visibilityCallback;
    }

    /**
     * Check if action/edit authorization is enabled.
     *
     * @return bool
     */
    public function hasAuthorization(): bool
    {
        return $this->authorizeCallback !== null;
    }

    /**
     * Check if visibility authorization is enabled.
     *
     * @return bool
     */
    public function hasVisibilityAuthorization(): bool
    {
        return $this->visibilityCallback !== null;
    }
}
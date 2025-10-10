<?php

namespace NyonCode\LivewireTable\Actions;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

class Action implements Htmlable
{
    protected string $name;

    protected ?string $label = null;

    protected ?string $icon = null;

    protected ?string $color = 'primary';

    protected ?Closure $action = null;

    protected ?Modal $modal = null;

    protected bool $requiresConfirmation = false;

    protected ?string $confirmationTitle = null;

    protected ?string $confirmationText = null;

    /**
     * Create a new action.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = str($name)->headline()->toString();
    }

    /**
     * Create a new action.
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * Set the label.
     *
     *
     * @return $this
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the icon.
     *
     *
     * @return $this
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the color.
     *
     *
     * @return $this
     */
    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Set the action.
     *
     *
     * @return $this
     */
    public function action(Closure $action): static
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Set the modal.
     *
     *
     * @return $this
     */
    public function modal(Modal $modal): static
    {
        $this->modal = $modal;

        return $this;
    }

    /**
     * Require confirmation.
     *
     *
     * @return $this
     */
    public function requireConfirmation(
        string $title = 'Really?',
        string $text = 'This action cannot be undone.'
    ): static {
        $this->requiresConfirmation = true;
        $this->confirmationTitle = $title;
        $this->confirmationText = $text;

        return $this;
    }

    /**
     * Execute the action.
     */
    public function execute($record): mixed
    {
        if ($this->action) {
            return ($this->action)($record);
        }

        return null;
    }

    /**
     * Get the name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the icon.
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Get the color.
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Get the modal.
     */
    public function getModal(): ?Modal
    {
        return $this->modal;
    }

    /**
     * Check if the action requires confirmation.
     */
    public function requiresConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }

    /**
     * Get the confirmation title.
     */
    public function getConfirmationTitle(): ?string
    {
        return $this->confirmationTitle;
    }

    /**
     * Get the confi    rmation text.
     */
    public function getConfirmationText(): ?string
    {
        return $this->confirmationText;
    }

    /**
     * Render the action to HTML.
     *
     * @throws Throwable
     */
    public function toHtml(): string
    {
        return $this->render();
    }

    /**
     * Render the action.
     *
     * @throws Throwable
     */
    public function render(): string
    {
        return view('livewirei-table::components.table.actions.action', [
            'action' => $this,
        ])->render();
    }
}

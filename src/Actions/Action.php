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
     *
     * @param  string  $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = str($name)->headline()->toString();
    }

    /**
     * Create a new action.
     *
     * @param  string  $name
     *
     * @return static
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * Set the label.
     *
     * @param  string  $label
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
     * @param  string  $icon
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
     * @param  string  $color
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
     * @param  Closure  $action
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
     * @param  Modal  $modal
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
     * @param  string  $title
     * @param  string  $text
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
     *
     * @param $record
     *
     * @return mixed
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
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the icon.
     *
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Get the color.
     *
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Get the modal.
     *
     * @return Modal|null
     */
    public function getModal(): ?Modal
    {
        return $this->modal;
    }

    /**
     * Check if the action requires confirmation.
     *
     * @return bool
     */
    public function requiresConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }

    /**
     * Get the confirmation title.
     *
     * @return string|null
     */
    public function getConfirmationTitle(): ?string
    {
        return $this->confirmationTitle;
    }

    /**
     * Get the confi    rmation text.
     *
     * @return string|null
     */
    public function getConfirmationText(): ?string
    {
        return $this->confirmationText;
    }

    /**
     * Render the action to HTML.
     *
     * @throws Throwable
     *
     * @return string
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

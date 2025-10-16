<?php

namespace NyonCode\LivewireTable\Actions;

class Modal
{
    public string $type = 'info';

    public string $title;

    public string|null $description = null;

    public string|null $view = null;

    public array $data = [];

    public string|null $size = 'md';

    /**
     * Create a new modal.
     */
    public static function make(string $title): static
    {
        $modal = new static;
        $modal->title = $title;

        return $modal;
    }

    /**
     * Set the modal type to info.
     *
     *
     * @return $this
     */
    public function info(string $description): static
    {
        $this->type = 'info';
        $this->description = $description;

        return $this;
    }

    /**
     * Set the modal type to form.
     *
     *
     * @return $this
     */
    public function form(string $view, array $data = []): static
    {
        $this->type = 'form';
        $this->view = $view;
        $this->data = $data;

        return $this;
    }

    /**
     * Set the modal type to confirmation.
     *
     *
     * @return $this
     */
    public function confirmation(string $description): static
    {
        $this->type = 'confirmation';
        $this->description = $description;

        return $this;
    }

    /**
     * Set the modal size.
     *
     *
     * @return $this
     */
    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get the modal type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the modal title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the modal description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the modal view.
     */
    public function getView(): ?string
    {
        return $this->view;
    }

    /**
     * Get the modal data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the modal size.
     */
    public function getSize(): string
    {
        return $this->size;
    }
}

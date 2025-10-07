<?php

namespace NyonCode\LivewireTable\Concerns;

trait HasPagination
{
    protected int $perPage = 15;

    protected array $pageOptions = [15, 25, 50, 100];

    /**
     * Set per page.
     * Example: perPage(15)
     *
     *
     * @return $this
     */
    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Set page options.
     * Example: pageOptions([15, 25, 50, 100])
     *
     * @return $this
     */
    public function pageOptions(array $options): static
    {
        $this->pageOptions = $options;

        return $this;
    }

    /**
     * Get per page.
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get page options.
     */
    public function getPageOptions(): array
    {
        return $this->pageOptions;
    }
}

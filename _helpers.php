<?php

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;

if (! function_exists('view')) {
    function view(string $view = '', array $data = [], array $mergeData = []): View|Factory
    {
        /** @phpstan-ignore-next-line */
        return Arr::first([]); // stub pro IDE
    }
}

if (! function_exists('app')) {
    /**
     * @return mixed
     */
    function app(?string $abstract = null, array $parameters = [])
    {
        return null;
    }
}

if (! function_exists('route')) {
    /**
     * @return string
     */
    function route(string $name, array $parameters = [], bool $absolute = true)
    {
        return '';
    }
}

if (! function_exists('redirect')) {
    function redirect(?string $to = null, int $status = 302, array $headers = [], ?bool $secure = null): Redirector|RedirectResponse
    {
        /** @phpstan-ignore-next-line */
        return Arr::first([]); // stub pro IDE
    }
}

if (! function_exists('config')) {
    /**
     * @return mixed
     */
    function config(?string $key = null, mixed $default = null)
    {
        return null;
    }
}

if (! function_exists('__')) {
    function __(string $key = '', array $replace = [], ?string $locale = null): string
    {
        return '';
    }
}

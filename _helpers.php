<?php

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Routing\Redirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

if (!function_exists('view')) {
    /**
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return View|Factory
     */
    function view(string $view = "",array $data = [],array $mergeData = []): View|Factory
    {
        /** @phpstan-ignore-next-line */
        return Arr::first([]); // stub pro IDE
    }
}

if (!function_exists('app')) {
    /**
     * @param string|null $abstract
     * @param array $parameters
     * @return mixed
     */
    function app(?string $abstract = null, array $parameters = [])
    {
        return null;
    }
}

if (!function_exists('route')) {
    /**
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function route(string $name,array $parameters = [],bool $absolute = true)
    {
        return '';
    }
}

if (!function_exists('redirect')) {
    /**
     * @param string|null $to
     * @param int $status
     * @param array $headers
     * @param bool|null $secure
     * @return Redirector|RedirectResponse
     */
    function redirect(?string $to = null,int $status = 302,array $headers = [],?bool $secure = null): Redirector|RedirectResponse
    {
        /** @phpstan-ignore-next-line */
        return Arr::first([]); // stub pro IDE
    }
}

if (!function_exists('config')) {
    /**
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function config(?string $key = null,mixed $default = null)
    {
        return null;
    }
}

if (!function_exists('__')) {
    /**
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    function __(string $key = '',array $replace = [],?string $locale = null): string
    {
        return '';
    }
}

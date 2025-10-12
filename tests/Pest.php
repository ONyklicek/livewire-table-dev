<?php

declare(strict_types=1);

use NyonCode\LivewireTable\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/


pest()->extend(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeValidRelationship', function () {
    return $this->toMatch('/^[a-z_]+(\.[a-z_]+)+$/');
});

expect()->extend('toHaveRelationships', function (int $count) {
    return $this->toBeArray()->toHaveCount($count);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function something(): void
{
    // ..
}
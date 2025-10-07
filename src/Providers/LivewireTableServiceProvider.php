<?php

namespace NyonCode\LivewireTable\Providers;

use NyonCode\LaravelPackageToolkit\Contracts\Packable;
use NyonCode\LaravelPackageToolkit\Packager;
use NyonCode\LaravelPackageToolkit\PackageServiceProvider;
use Exception;


class LivewireTableServiceProvider extends PackageServiceProvider implements Packable
{
    /**
     * Configures the package. This method is called after the package is registered.
     *
     * @param Packager $packager
     *
     * @return void
     * @throws Exception
     */
    final public function configure(Packager $packager): void
    {
        $packager->name('Laravel Livewire Table')
            ->hasViews()
            ->hasTranslations()
            ->hasAbout();
    }

    final public function aboutData(): array
    {
        return [
            'Author' => 'Ondrej Nyklicek',
            'Author Email' => 'ondrej@nyoncode.cz',
            'Author URL' => 'https://nyoncode.cz',
            'License' => 'MIT',
            'License URL' => 'https://opensource.org/licenses/MIT',
            'Description' => 'Livewire table',
        ];
    }
}
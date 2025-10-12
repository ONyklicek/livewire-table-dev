<?php

namespace NyonCode\LivewireTable\Providers;

use Exception;
use NyonCode\LaravelPackageToolkit\Contracts\Packable;
use NyonCode\LaravelPackageToolkit\Packager;
use NyonCode\LaravelPackageToolkit\PackageServiceProvider;

class LivewireTableServiceProvider extends PackageServiceProvider implements Packable
{
    /**
     * Configures the package. This method is called after the package is registered.
     *
     *
     * @throws Exception
     */
    final public function configure(Packager $packager): void
    {
        $packager->name('Livewire Table')
            ->hasConfig()
            ->hasMigrations()
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

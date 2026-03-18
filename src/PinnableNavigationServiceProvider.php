<?php

namespace SalmanHijazi\PinnableNavigation;

use Filament\Panel;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PinnableNavigationServiceProvider extends PackageServiceProvider
{
    public static string $name = 'pinnable-navigation';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasMigration('create_pinned_navigation_items_table')
            ->runsMigrations()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        Panel::macro('pinnableNavigation', function (): Panel {
            /** @var Panel $this */
            if ($this->hasPlugin('pinnable-navigation')) {
                return $this;
            }

            return $this->plugin(PinnableNavigationPlugin::make());
        });
    }
}


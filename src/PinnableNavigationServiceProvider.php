<?php

namespace Devletes\FilamentPinnableNavigation;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
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
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('pinnable-navigation', __DIR__.'/../dist/pinnable-navigation.css'),
            Js::make('pinnable-navigation', __DIR__.'/../dist/pinnable-navigation.js'),
        ], 'devletes/filament-pinnable-navigation');
    }
}

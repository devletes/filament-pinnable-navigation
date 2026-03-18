<?php

use Devletes\FilamentPinnableNavigation\Livewire\PinnableSidebar;
use Devletes\FilamentPinnableNavigation\PinnableNavigationPlugin;
use Filament\Panel;
use Workbench\App\Providers\Filament\AdminPanelProvider;

it('registers pinnable navigation as a Filament panel plugin', function (): void {
    $panel = Panel::make()
        ->id('test')
        ->path('test')
        ->plugin(PinnableNavigationPlugin::make());

    expect($panel->hasPlugin('pinnable-navigation'))->toBeTrue()
        ->and($panel->getSidebarLivewireComponent())->toBe(PinnableSidebar::class);
});

it('wires the workbench admin panel through the plugin', function (): void {
    $panel = (new AdminPanelProvider(app()))->panel(Panel::make());

    expect($panel->hasPlugin('pinnable-navigation'))->toBeTrue()
        ->and($panel->getSidebarLivewireComponent())->toBe(PinnableSidebar::class);
});

it('loads the package config', function (): void {
    expect(config('pinnable-navigation.database_enabled'))->toBeFalse()
        ->and(config('pinnable-navigation.table_name'))->toBe('pinned_navigation_items')
        ->and(config('pinnable-navigation.group_title'))->toBe('Pinned')
        ->and(config('pinnable-navigation.group_icon'))->toBe('heroicon-o-star')
        ->and(config('pinnable-navigation.pin_icon'))->toBe('heroicon-o-star')
        ->and(config('pinnable-navigation.unpin_icon'))->toBe('heroicon-s-star')
        ->and(config('pinnable-navigation.show_in_resource'))->toBeTrue()
        ->and(config('pinnable-navigation.accordion_mode'))->toBeTrue();
});

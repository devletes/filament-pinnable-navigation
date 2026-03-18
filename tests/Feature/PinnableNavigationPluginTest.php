<?php

use Filament\Panel;
use SalmanHijazi\PinnableNavigation\Livewire\PinnableSidebar;
use SalmanHijazi\PinnableNavigation\PinnableNavigationPlugin;
use Workbench\App\Providers\Filament\AdminPanelProvider;

it('registers pinnable navigation through the standard panel plugin api', function (): void {
    $panel = Panel::make()
        ->id('test')
        ->path('test')
        ->plugin(PinnableNavigationPlugin::make());

    expect($panel->hasPlugin('pinnable-navigation'))->toBeTrue()
        ->and($panel->getSidebarLivewireComponent())->toBe(PinnableSidebar::class);
});

it('keeps the panel macro available as a convenience alias', function (): void {
    $panel = Panel::make()
        ->id('test')
        ->path('test')
        ->pinnableNavigation();

    expect($panel->hasPlugin('pinnable-navigation'))->toBeTrue()
        ->and($panel->getSidebarLivewireComponent())->toBe(PinnableSidebar::class);
});

it('wires the workbench admin panel through plugin registration', function (): void {
    $panel = (new AdminPanelProvider(app()))->panel(Panel::make());

    expect($panel->hasPlugin('pinnable-navigation'))->toBeTrue()
        ->and($panel->getSidebarLivewireComponent())->toBe(PinnableSidebar::class);
});

it('loads the package config', function (): void {
    expect(config('pinnable-navigation.table_name'))->toBe('pinned_navigation_items');
});

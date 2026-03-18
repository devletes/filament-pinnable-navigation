<?php

namespace Devletes\FilamentPinnableNavigation;

use Devletes\FilamentPinnableNavigation\Livewire\PinnableSidebar;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;

class PinnableNavigationPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'pinnable-navigation';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->sidebarLivewireComponent(PinnableSidebar::class)
            ->renderHook(
                PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER,
                fn (): View => view('pinnable-navigation::panels.page-navigation-pin'),
            );
    }

    public function boot(Panel $panel): void {}
}

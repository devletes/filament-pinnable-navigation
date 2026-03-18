<?php

namespace SalmanHijazi\PinnableNavigation\Livewire;

use Filament\Facades\Filament;
use Filament\Livewire\Sidebar;
use Filament\Navigation\NavigationGroup;
use Illuminate\Contracts\View\View;
use SalmanHijazi\PinnableNavigation\Support\Navigation\PanelNavigationBuilder;
use SalmanHijazi\PinnableNavigation\Support\Navigation\UserNavigationPinService;

class PinnableSidebar extends Sidebar
{
    /**
     * @return array<NavigationGroup>
     */
    public function getNavigation(): array
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $user = Filament::auth()->user();

        if (! $user) {
            return [];
        }

        return app(PanelNavigationBuilder::class)->build($panel, $user);
    }

    public function togglePin(string $navigationKey): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $user = Filament::auth()->user();

        if (! $user) {
            return;
        }

        app(UserNavigationPinService::class)
            ->toggle($user, $panel->getId(), $navigationKey);

        $this->dispatch('refresh-sidebar');
        $this->dispatch('refresh-navigation-pin');
    }

    public function render(): View
    {
        return view('pinnable-navigation::livewire.pinnable-sidebar');
    }
}

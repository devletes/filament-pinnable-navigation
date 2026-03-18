<?php

namespace SalmanHijazi\PinnableNavigation\Livewire;

use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use SalmanHijazi\PinnableNavigation\Support\Navigation\NavigationKeyResolver;
use SalmanHijazi\PinnableNavigation\Support\Navigation\PanelNavigationBuilder;
use SalmanHijazi\PinnableNavigation\Support\Navigation\UserNavigationPinService;

class PageNavigationPin extends Component
{
    public ?string $navigationKey = null;

    public bool $isPinned = false;

    public bool $isVisible = false;

    public function mount(): void
    {
        $this->hydrateState();
    }

    #[On('refresh-navigation-pin')]
    public function refreshPin(): void
    {
        $this->hydrateState();
    }

    public function toggle(): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $user = Filament::auth()->user();

        if (! $user || blank($this->navigationKey)) {
            return;
        }

        app(UserNavigationPinService::class)
            ->toggle($user, $panel->getId(), $this->navigationKey);

        $this->hydrateState();
        $this->dispatch('refresh-sidebar');
    }

    public function render(): View
    {
        return view('pinnable-navigation::livewire.page-navigation-pin');
    }

    protected function hydrateState(): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $user = Filament::auth()->user();

        $this->navigationKey = null;
        $this->isPinned = false;
        $this->isVisible = false;

        if (! $user) {
            return;
        }

        $currentKey = app(NavigationKeyResolver::class)
            ->resolveCurrentPageKey($panel);

        if (blank($currentKey)) {
            return;
        }

        $item = app(PanelNavigationBuilder::class)
            ->findItemByKey($panel, $user, $currentKey);

        if (! $item || $item->getExtraAttributeBag()->get('data-pinnable') !== '1') {
            return;
        }

        $this->navigationKey = $currentKey;
        $this->isPinned = app(UserNavigationPinService::class)
            ->isPinned($user, $panel->getId(), $currentKey);
        $this->isVisible = true;
    }
}

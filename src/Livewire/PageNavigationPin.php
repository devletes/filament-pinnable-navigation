<?php

namespace Devletes\FilamentPinnableNavigation\Livewire;

use Devletes\FilamentPinnableNavigation\Support\Navigation\NavigationKeyResolver;
use Devletes\FilamentPinnableNavigation\Support\Navigation\PanelNavigationBuilder;
use Devletes\FilamentPinnableNavigation\Support\Navigation\PinPersistenceManager;
use Devletes\FilamentPinnableNavigation\Support\Navigation\UserNavigationPinService;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class PageNavigationPin extends Component
{
    public ?string $navigationKey = null;

    public ?string $localStorageKey = null;

    public bool $isPinned = false;

    public bool $isVisible = false;

    public bool $usesDatabase = false;

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
        if (! $this->usesDatabase) {
            return;
        }

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
        $persistenceManager = app(PinPersistenceManager::class);

        $this->navigationKey = null;
        $this->localStorageKey = null;
        $this->isPinned = false;
        $this->isVisible = false;
        $this->usesDatabase = $persistenceManager->usesDatabase();

        if (! $user) {
            return;
        }

        $this->localStorageKey = $persistenceManager->getLocalStorageKey($panel, $user);

        $currentKey = app(NavigationKeyResolver::class)
            ->resolveCurrentPageKey($panel);

        if (blank($currentKey)) {
            return;
        }

        if (str_starts_with($currentKey, 'resource:') && ! config('pinnable-navigation.show_in_resource', true)) {
            return;
        }

        $item = app(PanelNavigationBuilder::class)
            ->findItemByKey($panel, $user, $currentKey);

        if (! $item || $item->getExtraAttributeBag()->get('data-pinnable') !== '1') {
            return;
        }

        $this->navigationKey = $currentKey;
        $this->isPinned = $this->usesDatabase
            ? app(UserNavigationPinService::class)->isPinned($user, $panel->getId(), $currentKey)
            : false;
        $this->isVisible = true;
    }
}

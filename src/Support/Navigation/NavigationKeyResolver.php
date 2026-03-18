<?php

namespace Devletes\FilamentPinnableNavigation\Support\Navigation;

use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Illuminate\Support\Arr;

use function Filament\Support\original_request;

class NavigationKeyResolver
{
    public function forPage(string $class): string
    {
        return 'page:'.$this->normalizeClassName($class);
    }

    public function forResource(string $class): string
    {
        return 'resource:'.$this->normalizeClassName($class);
    }

    public function resolveFromItem(NavigationItem $item, Panel $panel): ?string
    {
        $key = Arr::get($item->getExtraAttributes(), 'data-navigation-key');

        return is_string($key) ? $key : null;
    }

    public function resolveCurrentPageKey(Panel $panel): ?string
    {
        foreach ($panel->getPages() as $pageClass) {
            if (! $pageClass::shouldRegisterNavigation()) {
                continue;
            }

            if (! $pageClass::canAccess()) {
                continue;
            }

            if (! original_request()->routeIs($pageClass::getNavigationItemActiveRoutePattern())) {
                continue;
            }

            return $this->forPage($pageClass);
        }

        foreach ($panel->getResources() as $resourceClass) {
            if (! $resourceClass::shouldRegisterNavigation()) {
                continue;
            }

            if ($resourceClass::getParentResourceRegistration()) {
                continue;
            }

            if (! $resourceClass::canAccess()) {
                continue;
            }

            if (! $resourceClass::hasPage('index')) {
                continue;
            }

            if (! original_request()->routeIs($resourceClass::getNavigationItemActiveRoutePattern())) {
                continue;
            }

            return $this->forResource($resourceClass);
        }

        return null;
    }

    protected function normalizeClassName(string $class): string
    {
        return str_replace('\\', '', ltrim($class, '\\'));
    }
}

<?php

namespace Devletes\FilamentPinnableNavigation\Support\Navigation;

use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;

class PinPersistenceManager
{
    public function usesDatabase(): bool
    {
        return (bool) config('pinnable-navigation.database_enabled');
    }

    public function getLocalStorageKey(Panel $panel, ?Authenticatable $user): ?string
    {
        if (! $user) {
            return null;
        }

        return implode(':', [
            'pinnable-navigation',
            'pins',
            $panel->getId(),
            str_replace('\\', '.', $user->getMorphClass()),
            (string) $user->getAuthIdentifier(),
        ]);
    }
}

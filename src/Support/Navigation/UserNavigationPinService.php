<?php

namespace SalmanHijazi\PinnableNavigation\Support\Navigation;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use SalmanHijazi\PinnableNavigation\Models\PinnableNavigationPin;

class UserNavigationPinService
{
    public function getPinnedKeys(Authenticatable $user, string $panelId): Collection
    {
        return PinnableNavigationPin::query()
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getAuthIdentifier())
            ->where('panel_id', $panelId)
            ->orderBy('id')
            ->pluck('navigation_key');
    }

    public function isPinned(Authenticatable $user, string $panelId, string $key): bool
    {
        $key = $this->normalizeKey($key);

        return PinnableNavigationPin::query()
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getAuthIdentifier())
            ->where('panel_id', $panelId)
            ->where('navigation_key', $key)
            ->exists();
    }

    public function pin(Authenticatable $user, string $panelId, string $key): void
    {
        $key = $this->normalizeKey($key);

        PinnableNavigationPin::query()->firstOrCreate([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getAuthIdentifier(),
            'panel_id' => $panelId,
            'navigation_key' => $key,
        ]);
    }

    public function unpin(Authenticatable $user, string $panelId, string $key): void
    {
        $key = $this->normalizeKey($key);

        PinnableNavigationPin::query()
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getAuthIdentifier())
            ->where('panel_id', $panelId)
            ->where('navigation_key', $key)
            ->delete();
    }

    public function toggle(Authenticatable $user, string $panelId, string $key): bool
    {
        $key = $this->normalizeKey($key);

        if ($this->isPinned($user, $panelId, $key)) {
            $this->unpin($user, $panelId, $key);

            return false;
        }

        $this->pin($user, $panelId, $key);

        return true;
    }

    protected function normalizeKey(string $key): string
    {
        [$prefix, $value] = array_pad(explode(':', $key, 2), 2, null);

        if (blank($value)) {
            return $key;
        }

        if (in_array($prefix, ['page', 'resource'], true)) {
            return $prefix.':'.str_replace('\\', '', ltrim($value, '\\'));
        }

        return $key;
    }
}

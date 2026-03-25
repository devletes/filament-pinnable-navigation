<?php

namespace Devletes\FilamentPinnableNavigation\Support\Navigation;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use UnitEnum;

class PanelNavigationBuilder
{
    public function __construct(
        protected NavigationKeyResolver $keyResolver,
        protected UserNavigationPinService $pinService,
    ) {}

    /**
     * @return array<NavigationGroup>
     */
    public function build(Panel $panel, Authenticatable $user): array
    {
        $panelId = $panel->getId();
        $pinnedKeys = $this->pinService->getPinnedKeys($user, $panelId)->flip();
        $sourceItems = $this->getSourceItems($panel);
        $groups = $this->groupItems($panel, $sourceItems);

        $topLevelGroup = null;
        $labeledGroups = [];
        $pinnedItems = collect();

        foreach ($groups as $group) {
            $items = collect($group->getItems())
                ->map(function (NavigationItem $item) use ($panel, $pinnedKeys, &$pinnedItems): NavigationItem {
                    $key = $this->keyResolver->resolveFromItem($item, $panel);
                    $isPinned = filled($key) && $pinnedKeys->has($key);

                    if ($this->isPinnable($item) && $isPinned) {
                        $pinnedItems->push($this->clonePinnedItem($item, $key));
                    }

                    return $this->markItem($item, $key, $isPinned, false);
                })
                ->all();

            $group->items($items);

            if (blank($group->getLabel())) {
                $topLevelGroup = $group;

                continue;
            }

            $labeledGroups[] = $group;
        }

        $navigation = [];

        if ($topLevelGroup instanceof NavigationGroup) {
            $navigation[] = $this->markGroup($topLevelGroup);
        }

        $pinnedGroup = $this->makePinnedGroup($pinnedItems);

        if ($pinnedGroup instanceof NavigationGroup) {
            $navigation[] = $pinnedGroup;
        }

        foreach ($labeledGroups as $group) {
            $navigation[] = $this->markGroup($group);
        }

        return $navigation;
    }

    public function resolveCurrentItem(Panel $panel, Authenticatable $user): ?NavigationItem
    {
        $currentKey = $this->keyResolver->resolveCurrentPageKey($panel);

        if (blank($currentKey)) {
            return null;
        }

        return $this->findItemByKey($panel, $user, $currentKey);
    }

    public function findItemByKey(Panel $panel, Authenticatable $user, string $navigationKey): ?NavigationItem
    {
        $groups = $this->build($panel, $user);

        foreach ($groups as $group) {
            foreach ($group->getItems() as $item) {
                if ($this->keyResolver->resolveFromItem($item, $panel) === $navigationKey) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * @return array<NavigationItem>
     */
    protected function getSourceItems(Panel $panel): array
    {
        $items = [];
        $seenItems = [];

        foreach ($panel->getPages() as $pageClass) {
            if (filled($pageClass::getCluster())) {
                continue;
            }

            if (! $pageClass::shouldRegisterNavigation() || ! $pageClass::canAccess()) {
                continue;
            }

            foreach ($pageClass::getNavigationItems() as $item) {
                $this->pushSourceItem(
                    $items,
                    $seenItems,
                    $item,
                    $this->keyResolver->forPage($pageClass),
                );
            }
        }

        foreach ($panel->getResources() as $resourceClass) {
            if (filled($resourceClass::getCluster())) {
                continue;
            }

            if (! $resourceClass::shouldRegisterNavigation()) {
                continue;
            }

            if ($resourceClass::getParentResourceRegistration()) {
                continue;
            }

            if (! $resourceClass::canAccess() || ! $resourceClass::hasPage('index')) {
                continue;
            }

            foreach ($resourceClass::getNavigationItems() as $item) {
                $this->pushSourceItem(
                    $items,
                    $seenItems,
                    $item,
                    $this->keyResolver->forResource($resourceClass),
                );
            }
        }

        foreach ($panel->getNavigationItems() as $item) {
            $this->pushSourceItem($items, $seenItems, $item);
        }

        return $items;
    }

    /**
     * @param  array<NavigationItem>  $items
     * @return array<NavigationGroup>
     */
    protected function groupItems(Panel $panel, array $items): array
    {
        $registeredGroups = collect($panel->getNavigationGroups());

        return collect($items)
            ->filter(fn (NavigationItem $item): bool => $item->isVisible())
            ->sortBy(fn (NavigationItem $item): int => (int) ($item->getSort() ?? 0))
            ->groupBy(fn (NavigationItem $item): string => serialize($item->getGroup()))
            ->map(function (Collection $groupedItems, string $serializedGroup) use ($registeredGroups): NavigationGroup {
                $parentItems = $groupedItems->groupBy(fn (NavigationItem $item): string => $item->getParentItem() ?? '');

                $items = $parentItems->get('', collect())
                    ->groupBy(fn (NavigationItem $item): string => (string) $item->getLabel())
                    ->map(fn (Collection $items): NavigationItem => $this->resolvePreferredNavigationItem($items))
                    ->keyBy(fn (NavigationItem $item): string => (string) $item->getLabel());

                $parentItems->except([''])->each(function (Collection $children, string $parentLabel) use ($items): void {
                    if (! $items->has($parentLabel)) {
                        return;
                    }

                    $items->get($parentLabel)->childItems($children->values()->all());
                });

                $items = $items
                    ->filter(fn (NavigationItem $item): bool => filled($item->getChildItems()) || filled($item->getUrl()))
                    ->values()
                    ->all();

                $groupName = unserialize($serializedGroup);

                if (blank($groupName)) {
                    return NavigationGroup::make()->items($items);
                }

                $groupEnum = null;

                if ($groupName instanceof UnitEnum) {
                    $groupEnum = $groupName;
                    $groupName = $groupEnum->name;
                }

                $registeredGroup = $registeredGroups->first(function (NavigationGroup|string $registeredGroup, string|int $registeredGroupIndex) use ($groupName) {
                    if ($registeredGroupIndex === $groupName) {
                        return true;
                    }

                    if ($registeredGroup === $groupName) {
                        return true;
                    }

                    if (! $registeredGroup instanceof NavigationGroup) {
                        return false;
                    }

                    return $registeredGroup->getLabel() === $groupName;
                });

                if ($registeredGroup instanceof NavigationGroup) {
                    return $registeredGroup->items($items);
                }

                if ($groupEnum instanceof UnitEnum) {
                    return NavigationGroup::fromEnum($groupEnum)->items($items);
                }

                return NavigationGroup::make($registeredGroup ?? $groupName)->items($items);
            })
            ->filter(fn (NavigationGroup $group): bool => filled($group->getItems()))
            ->sortBy(function (NavigationGroup $group, ?string $serializedGroup) use ($registeredGroups): int {
                if (blank($group->getLabel())) {
                    return -1;
                }

                $groupName = unserialize((string) $serializedGroup);

                if ($groupName instanceof UnitEnum) {
                    $groupName = $groupName->name;
                }

                $groupsToSearch = $registeredGroups->all();

                if ($registeredGroups->first() instanceof NavigationGroup) {
                    $groupsToSearch = [
                        ...array_keys($registeredGroups->all()),
                        ...array_map(
                            fn (NavigationGroup $registeredGroup): ?string => $registeredGroup->getLabel(),
                            array_values($registeredGroups->all()),
                        ),
                    ];
                }

                $position = array_search($groupName, $groupsToSearch, true);

                return $position === false ? count($groupsToSearch) : $position;
            })
            ->values()
            ->all();
    }

    protected function makePinnedGroup(Collection $items): ?NavigationGroup
    {
        $items = $items
            ->filter(fn (NavigationItem $item): bool => $item->isVisible())
            ->sortBy(fn (NavigationItem $item): int => (int) ($item->getSort() ?? 0))
            ->values();

        if ($items->isEmpty()) {
            return null;
        }

        return $this->markGroup(
            NavigationGroup::make((string) config('pinnable-navigation.group_title', __('pinnable-navigation::pinnable-navigation.group_label')))
                ->icon(config('pinnable-navigation.group_icon', 'heroicon-o-star'))
                ->items($items->all())
        );
    }

    protected function isPinnable(NavigationItem $item): bool
    {
        return $this->canMarkAsPinnable(
            $item,
            Arr::get($item->getExtraAttributes(), 'data-navigation-key'),
        );
    }

    protected function clonePinnedItem(NavigationItem $item, ?string $key): NavigationItem
    {
        return $this->markItem(clone $item, $key, true, true);
    }

    protected function markItem(NavigationItem $item, ?string $key, bool $isPinned, bool $isPinnedClone): NavigationItem
    {
        return $item->extraAttributes([
            ...$item->getExtraAttributes(),
            'data-navigation-key' => $key,
            'data-pinnable' => $this->canMarkAsPinnable($item, $key) ? '1' : '0',
            'data-pinned' => $isPinned ? '1' : '0',
            'data-pinned-clone' => $isPinnedClone ? '1' : '0',
        ]);
    }

    protected function canMarkAsPinnable(NavigationItem $item, ?string $key): bool
    {
        if (blank($item->getGroup())) {
            return false;
        }

        if (blank($key) || blank($item->getUrl())) {
            return false;
        }

        if (filled($item->getChildItems())) {
            return false;
        }

        return $key !== 'page:FilamentPagesDashboard';
    }

    protected function markGroup(NavigationGroup $group): NavigationGroup
    {
        if (! config('pinnable-navigation.accordion_mode', true)) {
            return $group->extraSidebarAttributes([
                ...$group->getExtraSidebarAttributes(),
                'data-accordion-id' => null,
                'data-accordion-managed' => '0',
            ]);
        }

        $label = $group->getLabel();
        $accordionId = blank($label) ? null : 'group:'.str((string) $label)->slug('-');

        return $group->extraSidebarAttributes([
            ...$group->getExtraSidebarAttributes(),
            'data-accordion-id' => $accordionId,
            'data-accordion-managed' => filled($accordionId) ? '1' : '0',
        ]);
    }

    /**
     * @param  array<NavigationItem>  $items
     * @param  array<string, true>  $seenItems
     */
    protected function pushSourceItem(array &$items, array &$seenItems, NavigationItem $item, ?string $key = null): void
    {
        $identity = $this->getNavigationItemIdentity($item, $key);

        if (array_key_exists($identity, $seenItems)) {
            return;
        }

        $items[] = $this->markItem(clone $item, $key, false, false);
        $seenItems[$identity] = true;
    }

    protected function getNavigationItemIdentity(NavigationItem $item, ?string $key = null): string
    {
        $key ??= Arr::get($item->getExtraAttributes(), 'data-navigation-key');

        if (filled($key)) {
            return "key:{$key}";
        }

        return implode('|', [
            'fallback',
            (string) $item->getGroup(),
            (string) $item->getParentItem(),
            (string) $item->getLabel(),
            (string) $item->getUrl(),
        ]);
    }

    /**
     * @param  Collection<int, NavigationItem>  $items
     */
    protected function resolvePreferredNavigationItem(Collection $items): NavigationItem
    {
        return $items
            ->sortByDesc(fn (NavigationItem $item): int => filled(Arr::get($item->getExtraAttributes(), 'data-navigation-key')) ? 1 : 0)
            ->first();
    }
}

<div>
    @php
        $navigation = $this->getNavigation();
        $accordionEnabled = (bool) config('pinnable-navigation.accordion_mode', true);
        $accordionGroupIds = $accordionEnabled
            ? collect($navigation)
                ->map(fn (\Filament\Navigation\NavigationGroup $group) => $group->getExtraSidebarAttributeBag()->get('data-accordion-id'))
                ->filter()
                ->values()
                ->all()
            : [];
        $defaultCollapsedGroups = $accordionEnabled
            ? $accordionGroupIds
            : collect($navigation)
                ->filter(fn (\Filament\Navigation\NavigationGroup $group): bool => $group->isCollapsed())
                ->map(fn (\Filament\Navigation\NavigationGroup $group): string => $group->getExtraSidebarAttributeBag()->get('data-accordion-id') ?? $group->getLabel())
                ->filter()
                ->values()
                ->all();
        $isRtl = __('filament-panels::layout.direction') === 'rtl';
        $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
        $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
        $hasNavigation = filament()->hasNavigation();
        $hasTopbar = filament()->hasTopbar();
        $panel = filament()->getCurrentPanel();
        $user = filament()->auth()->user();
        $persistenceManager = app(\Devletes\FilamentPinnableNavigation\Support\Navigation\PinPersistenceManager::class);
        $usesDatabase = $persistenceManager->usesDatabase();
        $localStorageKey = $panel && $user ? $persistenceManager->getLocalStorageKey($panel, $user) : null;
        $hasTopLevelGroup = filled($navigation) && blank($navigation[0]->getLabel());
    @endphp

    <aside
        x-data="{}"
        @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
            x-cloak
        @else
            x-cloak="-lg"
        @endif
        x-bind:class="{ 'fi-sidebar-open': $store.sidebar.isOpen }"
        class="fi-sidebar fi-main-sidebar"
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_START) }}

        <div class="fi-sidebar-header-ctn">
            <header class="fi-sidebar-header">
                @if ((! $hasTopbar) && $isSidebarCollapsibleOnDesktop)
                    <x-filament::icon-button
                        color="gray"
                        :icon="$isRtl ? \Filament\Support\Icons\Heroicon::OutlinedChevronLeft : \Filament\Support\Icons\Heroicon::OutlinedChevronRight"
                        :icon-alias="
                            $isRtl
                            ? [
                                \Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL,
                                \Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON,
                            ]
                            : \Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON
                        "
                        icon-size="lg"
                        :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                        x-cloak
                        x-data="{}"
                        x-on:click="$store.sidebar.open()"
                        x-show="! $store.sidebar.isOpen"
                        class="fi-sidebar-open-collapse-sidebar-btn"
                    />
                @endif

                @if ((! $hasTopbar) && ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop))
                    <x-filament::icon-button
                        color="gray"
                        :icon="$isRtl ? \Filament\Support\Icons\Heroicon::OutlinedChevronRight : \Filament\Support\Icons\Heroicon::OutlinedChevronLeft"
                        :icon-alias="
                            $isRtl
                            ? [
                                \Filament\View\PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL,
                                \Filament\View\PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON,
                            ]
                            : \Filament\View\PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON
                        "
                        icon-size="lg"
                        :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                        x-cloak
                        x-data="{}"
                        x-on:click="$store.sidebar.close()"
                        x-show="$store.sidebar.isOpen"
                        class="fi-sidebar-close-collapse-sidebar-btn"
                    />
                @endif

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_LOGO_BEFORE) }}

                <div x-show="$store.sidebar.isOpen" class="fi-sidebar-header-logo-ctn">
                    @if ($homeUrl = filament()->getHomeUrl())
                        <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
                            <x-filament-panels::logo />
                        </a>
                    @else
                        <x-filament-panels::logo />
                    @endif
                </div>

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_LOGO_AFTER) }}
            </header>
        </div>

        @if (filament()->hasTenancy() && filament()->hasTenantMenu())
            <x-filament-panels::tenant-menu />
        @endif

        @if (filament()->isGlobalSearchEnabled() && filament()->getGlobalSearchPosition() === \Filament\Enums\GlobalSearchPosition::Sidebar)
            <div
                @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
                    x-show="$store.sidebar.isOpen"
                @endif
            >
                @livewire(Filament\Livewire\GlobalSearch::class)
            </div>
        @endif

        <nav
            x-data="{
                accordionGroups: @js($accordionGroupIds),
                normalizeCollapsedGroups() {
                    const defaultCollapsedGroups = @js($defaultCollapsedGroups)
                    const currentCollapsedGroups = Array.isArray($store.sidebar.collapsedGroups)
                        ? $store.sidebar.collapsedGroups
                        : null

                    if (currentCollapsedGroups !== null) {
                        localStorage.setItem('collapsedGroups', JSON.stringify(currentCollapsedGroups))
                        return currentCollapsedGroups
                    }

                    $store.sidebar.collapsedGroups = defaultCollapsedGroups
                    localStorage.setItem('collapsedGroups', JSON.stringify(defaultCollapsedGroups))

                    return defaultCollapsedGroups
                },
            }"
            x-init="normalizeCollapsedGroups()"
            class="fi-sidebar-nav"
            data-accordion-enabled="{{ $accordionEnabled ? '1' : '0' }}"
            data-accordion-groups='@json($accordionGroupIds)'
            data-persistence-mode="{{ $usesDatabase ? 'database' : 'localstorage' }}"
            @if (filled($localStorageKey))
                data-localstorage-key="{{ $localStorageKey }}"
            @endif
        >
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_START) }}

            <ul class="fi-sidebar-nav-groups">
                @if ((! $usesDatabase) && filled($localStorageKey) && (! $hasTopLevelGroup))
                    @include('pinnable-navigation::sidebar.localstorage-pinned-group', [
                        'sidebarCollapsible' => $isSidebarCollapsibleOnDesktop,
                    ])
                @endif

                @foreach ($navigation as $group)
                    @include('pinnable-navigation::sidebar.group', [
                        'active' => $group->isActive(),
                        'attributes' => \Filament\Support\prepare_inherited_attributes($group->getExtraSidebarAttributeBag()),
                        'collapsible' => $group->isCollapsible(),
                        'icon' => $group->getIcon(),
                        'items' => $group->getItems(),
                        'label' => $group->getLabel(),
                    ])

                    @if ((! $usesDatabase) && filled($localStorageKey) && $loop->first && blank($group->getLabel()))
                        @include('pinnable-navigation::sidebar.localstorage-pinned-group', [
                            'sidebarCollapsible' => $isSidebarCollapsibleOnDesktop,
                        ])
                    @endif
                @endforeach
            </ul>

            <script>
                const sidebarNav = document.currentScript.closest('.fi-sidebar-nav')
                const accordionEnabled = sidebarNav?.dataset.accordionEnabled === '1'
                const accordionGroups = JSON.parse(sidebarNav?.dataset.accordionGroups ?? '[]')
                const parsedCollapsedGroups = JSON.parse(localStorage.getItem('collapsedGroups') ?? 'null')
                const collapsedGroups = Array.isArray(parsedCollapsedGroups)
                    ? parsedCollapsedGroups
                    : (accordionEnabled ? accordionGroups : @js($defaultCollapsedGroups))

                localStorage.setItem('collapsedGroups', JSON.stringify(collapsedGroups))

                document
                    .querySelectorAll('.fi-sidebar-group')
                    .forEach((group) => {
                        if (! collapsedGroups.includes(group.dataset.groupLabel)) {
                            return
                        }

                        group.querySelector('.fi-sidebar-group-items').style.display = 'none'
                        group.classList.add('fi-collapsed')
                    })
            </script>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_END) }}
        </nav>

        @php
            $isAuthenticated = filament()->auth()->check();
            $hasDatabaseNotificationsInSidebar = filament()->hasDatabaseNotifications() && filament()->getDatabaseNotificationsPosition() === \Filament\Enums\DatabaseNotificationsPosition::Sidebar;
            $hasUserMenuInSidebar = filament()->hasUserMenu() && filament()->getUserMenuPosition() === \Filament\Enums\UserMenuPosition::Sidebar;
            $shouldRenderFooter = $isAuthenticated && ($hasDatabaseNotificationsInSidebar || $hasUserMenuInSidebar);
        @endphp

        @if ($shouldRenderFooter)
            <div class="fi-sidebar-footer">
                @if ($hasDatabaseNotificationsInSidebar)
                    @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                        'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                    ])
                @endif

                @if ($hasUserMenuInSidebar)
                    <x-filament-panels::user-menu />
                @endif
            </div>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_FOOTER) }}
    </aside>

    <x-filament-actions::modals />
</div>


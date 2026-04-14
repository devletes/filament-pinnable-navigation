@php
    $sidebarCollapsible ??= filament()->isSidebarCollapsibleOnDesktop();
    $groupIcon = config('pinnable-navigation.group_icon', 'heroicon-o-star');
    $groupLabel = 'group:pinned';
    $accordionManaged = (bool) config('pinnable-navigation.accordion_mode', true);
    $groupTitle = (string) config('pinnable-navigation.group_title', __('pinnable-navigation::pinnable-navigation.group_label'));
@endphp

<li
    x-data="{
        label: @js($groupLabel),
        accordionManaged: @js($accordionManaged),
        toggleGroup() {
            if (! this.accordionManaged) {
                $store.sidebar.toggleCollapsedGroup(this.label)
                return
            }

            const managedGroups = accordionGroups.includes(this.label)
                ? accordionGroups
                : [...accordionGroups, this.label]
            const collapsedGroups = Array.isArray($store.sidebar.collapsedGroups) ? $store.sidebar.collapsedGroups : []
            const nonAccordionCollapsedGroups = collapsedGroups.filter((group) => ! managedGroups.includes(group))
            const isCollapsed = $store.sidebar.groupIsCollapsed(this.label)

            $store.sidebar.collapsedGroups = isCollapsed
                ? [
                    ...nonAccordionCollapsedGroups,
                    ...managedGroups.filter((group) => group !== this.label),
                ]
                : [
                    ...nonAccordionCollapsedGroups,
                    ...managedGroups,
                ]
        },
    }"
    class="fi-sidebar-group fi-localstorage-pinned-group fi-collapsible"
    data-localstorage-pinned-group="1"
    data-group-label="{{ $groupLabel }}"
    x-bind:class="{ 'fi-collapsed': $store.sidebar.groupIsCollapsed(label) }"
    hidden
>
    <div
        x-on:click="toggleGroup()"
        @if ($sidebarCollapsible)
            x-show="$store.sidebar.isOpen"
            x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start"
            x-transition:enter-end="fi-transition-enter-end"
        @endif
        class="fi-sidebar-group-btn"
    >
        @if (filled($groupIcon))
            {{ \Filament\Support\generate_icon_html($groupIcon, size: \Filament\Support\Enums\IconSize::Large) }}
        @endif

        <span class="fi-sidebar-group-label">
            {{ $groupTitle }}
        </span>

        <x-filament::icon-button
            color="gray"
            :icon="\Filament\Support\Icons\Heroicon::ChevronUp"
            :icon-alias="\Filament\View\PanelsIconAlias::SIDEBAR_GROUP_COLLAPSE_BUTTON"
            :label="$groupTitle"
            x-bind:aria-expanded="! $store.sidebar.groupIsCollapsed(label)"
            x-on:click.stop="toggleGroup()"
            class="fi-sidebar-group-collapse-btn"
        />
    </div>

    @if ($sidebarCollapsible && filled($groupIcon))
        <x-filament::dropdown
            :placement="(__('filament-panels::layout.direction') === 'rtl') ? 'left-start' : 'right-start'"
            x-show="! $store.sidebar.isOpen"
        >
            <x-slot name="trigger">
                <button
                    x-data="{ tooltip: false }"
                    x-effect="
                        tooltip = $store.sidebar.isOpen
                            ? false
                            : {
                                  content: @js($groupTitle),
                                  placement: document.dir === 'rtl' ? 'left' : 'right',
                                  theme: $store.theme,
                              }
                    "
                    x-tooltip.html="tooltip"
                    class="fi-sidebar-group-dropdown-trigger-btn"
                >
                    {{ \Filament\Support\generate_icon_html($groupIcon, size: \Filament\Support\Enums\IconSize::Large) }}
                </button>
            </x-slot>

            <x-filament::dropdown.header>
                {{ $groupTitle }}
            </x-filament::dropdown.header>

            <x-filament::dropdown.list data-localstorage-pinned-dropdown-items>
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    @endif

    <ul
        @if ($sidebarCollapsible)
            x-show="$store.sidebar.isOpen ? ! $store.sidebar.groupIsCollapsed(label) : ! @js($sidebarCollapsible && filled($groupIcon))"
            x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start"
            x-transition:enter-end="fi-transition-enter-end"
        @else
            x-show="! $store.sidebar.groupIsCollapsed(label)"
        @endif
        x-collapse.duration.200ms
        class="fi-sidebar-group-items"
        data-localstorage-pinned-items
    ></ul>
</li>

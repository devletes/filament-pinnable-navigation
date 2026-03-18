@php
    $active ??= false;
    $attributes ??= new \Illuminate\View\ComponentAttributeBag();
    $collapsible ??= true;
    $icon ??= null;
    $items ??= [];
    $label ??= null;
    $sidebarCollapsible = filament()->isSidebarCollapsibleOnDesktop();
    $subNavigation ??= false;
    $hasDropdown = filled($label) && filled($icon) && $sidebarCollapsible;
    $groupLabel = $attributes->get('data-accordion-id') ?? ($subNavigation ? "sub_navigation_{$label}" : $label);
    $isAccordionManaged = $attributes->get('data-accordion-managed') === '1';
@endphp

<li
    x-data="{
        label: @js($groupLabel),
        accordionManaged: @js($isAccordionManaged),
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
    data-group-label="{{ $groupLabel }}"
    x-bind:class="{ 'fi-collapsed': $store.sidebar.groupIsCollapsed(label) }"
    {{
        $attributes->class([
            'fi-sidebar-group',
            'fi-active' => $active,
            'fi-collapsible' => $collapsible,
        ])
    }}
>
    @if ($label)
        <div
            @if ($collapsible)
                x-on:click="toggleGroup()"
            @endif
            @if ($sidebarCollapsible)
                x-show="$store.sidebar.isOpen"
                x-transition:enter="fi-transition-enter"
                x-transition:enter-start="fi-transition-enter-start"
                x-transition:enter-end="fi-transition-enter-end"
            @endif
            class="fi-sidebar-group-btn"
        >
            @if ($icon)
                {{ \Filament\Support\generate_icon_html($icon, size: \Filament\Support\Enums\IconSize::Large) }}
            @endif

            <span class="fi-sidebar-group-label">
                {{ $label }}
            </span>

            @if ($collapsible)
                <x-filament::icon-button
                    color="gray"
                    :icon="\Filament\Support\Icons\Heroicon::ChevronUp"
                    :icon-alias="\Filament\View\PanelsIconAlias::SIDEBAR_GROUP_COLLAPSE_BUTTON"
                    :label="$label"
                    x-bind:aria-expanded="! $store.sidebar.groupIsCollapsed(label)"
                    x-on:click.stop="toggleGroup()"
                    class="fi-sidebar-group-collapse-btn"
                />
            @endif
        </div>
    @endif

    @if ($hasDropdown)
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
                                  content: @js($label),
                                  placement: document.dir === 'rtl' ? 'left' : 'right',
                                  theme: $store.theme,
                              }
                    "
                    x-tooltip.html="tooltip"
                    class="fi-sidebar-group-dropdown-trigger-btn"
                >
                    {{ \Filament\Support\generate_icon_html($icon, size: \Filament\Support\Enums\IconSize::Large) }}
                </button>
            </x-slot>

            @php
                $lists = [];

                foreach ($items as $item) {
                    if ($childItems = $item->getChildItems()) {
                        $lists[] = [
                            $item,
                            ...$childItems,
                        ];
                        $lists[] = [];

                        continue;
                    }

                    if (empty($lists)) {
                        $lists[] = [$item];

                        continue;
                    }

                    $lists[count($lists) - 1][] = $item;
                }

                if (! empty($lists) && empty($lists[count($lists) - 1])) {
                    array_pop($lists);
                }
            @endphp

            @if (filled($label))
                <x-filament::dropdown.header>
                    {{ $label }}
                </x-filament::dropdown.header>
            @endif

            @foreach ($lists as $list)
                <x-filament::dropdown.list>
                    @foreach ($list as $item)
                        @php
                            $itemIsActive = $item->isActive();
                            $itemBadge = $item->getBadge();
                            $itemBadgeColor = $item->getBadgeColor();
                            $itemBadgeTooltip = $item->getBadgeTooltip();
                            $itemUrl = $item->getUrl();
                            $itemIcon = $itemIsActive ? ($item->getActiveIcon() ?? $item->getIcon()) : $item->getIcon();
                            $shouldItemOpenUrlInNewTab = $item->shouldOpenUrlInNewTab();
                            $itemExtraAttributes = $item->getExtraAttributeBag();
                        @endphp

                        <x-filament::dropdown.list.item
                            :badge="$itemBadge"
                            :badge-color="$itemBadgeColor"
                            :badge-tooltip="$itemBadgeTooltip"
                            :color="$itemIsActive ? 'primary' : 'gray'"
                            :href="$itemUrl"
                            :icon="$itemIcon"
                            tag="a"
                            :target="$shouldItemOpenUrlInNewTab ? '_blank' : null"
                            :attributes="\Filament\Support\prepare_inherited_attributes($itemExtraAttributes)"
                        >
                            {{ $item->getLabel() }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            @endforeach
        </x-filament::dropdown>
    @endif

    <ul
        @if (filled($label))
            @if ($sidebarCollapsible)
                x-show="$store.sidebar.isOpen ? ! $store.sidebar.groupIsCollapsed(label) : ! @js($hasDropdown)"
            @else
                x-show="! $store.sidebar.groupIsCollapsed(label)"
            @endif
            x-collapse.duration.200ms
        @endif
        @if ($sidebarCollapsible)
            x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start"
            x-transition:enter-end="fi-transition-enter-end"
        @endif
        class="fi-sidebar-group-items"
    >
        @foreach ($items as $item)
            @php
                $isItemChildItemsActive = $item->isChildItemsActive();
                $isItemActive = (! $isItemChildItemsActive) && $item->isActive();
                $itemActiveIcon = $item->getActiveIcon();
                $itemBadge = $item->getBadge();
                $itemBadgeColor = $item->getBadgeColor();
                $itemBadgeTooltip = $item->getBadgeTooltip();
                $itemChildItems = $item->getChildItems();
                $itemIcon = $item->getIcon();
                $shouldItemOpenUrlInNewTab = $item->shouldOpenUrlInNewTab();
                $itemUrl = $item->getUrl();
                $itemExtraAttributes = $item->getExtraAttributeBag();
                $itemNavigationKey = $itemExtraAttributes->get('data-navigation-key');
                $itemIsPinnable = str_contains(trim((string) $itemExtraAttributes->get('data-pinnable')), '1');
                $itemIsPinned = str_contains(trim((string) $itemExtraAttributes->get('data-pinned')), '1');

                if ($icon) {
                    $itemIcon = null;
                    $itemActiveIcon = null;
                }
            @endphp

            @include('pinnable-navigation::sidebar.item', [
                'active' => $isItemActive,
                'activeChildItems' => $isItemChildItemsActive,
                'activeIcon' => $itemActiveIcon,
                'attributes' => \Filament\Support\prepare_inherited_attributes($itemExtraAttributes),
                'badge' => $itemBadge,
                'badgeColor' => $itemBadgeColor,
                'badgeTooltip' => $itemBadgeTooltip,
                'childItems' => $itemChildItems,
                'first' => $loop->first,
                'grouped' => filled($label),
                'icon' => $itemIcon,
                'isPinned' => $itemIsPinned,
                'isPinnable' => $itemIsPinnable,
                'item' => $item,
                'label' => $item->getLabel(),
                'last' => $loop->last,
                'navigationKey' => $itemNavigationKey,
                'shouldOpenUrlInNewTab' => $shouldItemOpenUrlInNewTab,
                'sidebarCollapsible' => $sidebarCollapsible,
                'subNavigation' => $subNavigation,
                'url' => $itemUrl,
            ])
        @endforeach
    </ul>
</li>



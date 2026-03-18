@php
    $active ??= false;
    $activeChildItems ??= false;
    $activeIcon ??= null;
    $attributes ??= new \Illuminate\View\ComponentAttributeBag();
    $badge ??= null;
    $badgeColor ??= null;
    $badgeTooltip ??= null;
    $childItems ??= [];
    $first ??= false;
    $grouped ??= false;
    $icon ??= null;
    $item ??= null;
    $label ??= $item?->getLabel();
    $last ??= false;
    $shouldOpenUrlInNewTab ??= false;
    $sidebarCollapsible ??= true;
    $sidebarCollapsible = $sidebarCollapsible && filament()->isSidebarCollapsibleOnDesktop();
    $subGrouped ??= false;
    $subNavigation ??= false;
    $url ??= null;
    $navigationKey ??= $attributes->get('data-navigation-key');
    $isPinnable ??= str_contains(trim((string) $attributes->get('data-pinnable')), '1');
    $isPinned ??= str_contains(trim((string) $attributes->get('data-pinned')), '1');
@endphp

<li
    {{
        $attributes->class([
            'fi-sidebar-item',
            'fi-active' => $active,
            'fi-sidebar-item-has-active-child-items' => $activeChildItems,
            'fi-sidebar-item-has-url' => filled($url),
        ])
    }}
>
    <div class="fi-sidebar-item-row">
        <a
            {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab) }}
            x-on:click="window.matchMedia(`(max-width: 1024px)`).matches && $store.sidebar.close()"
            @if ($sidebarCollapsible && (! $subNavigation))
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
            @endif
            class="fi-sidebar-item-btn fi-sidebar-item-btn-with-pin"
        >
            @if (filled($icon) && ((! $subGrouped) || ($sidebarCollapsible && (! $subNavigation))))
                {{
                    \Filament\Support\generate_icon_html(($active && $activeIcon) ? $activeIcon : $icon, attributes: (new \Illuminate\View\ComponentAttributeBag([
                        'x-show' => ($subGrouped && $sidebarCollapsible) ? '! $store.sidebar.isOpen' : false,
                    ]))->class(['fi-sidebar-item-icon']), size: \Filament\Support\Enums\IconSize::Large)
                }}
            @endif

            @if ((blank($icon) && $grouped) || $subGrouped)
                <div
                    @if (filled($icon) && $subGrouped && $sidebarCollapsible && (! $subNavigation))
                        x-show="$store.sidebar.isOpen"
                    @endif
                    class="fi-sidebar-item-grouped-border"
                >
                    @if (! $first)
                        <div class="fi-sidebar-item-grouped-border-part-not-first"></div>
                    @endif

                    @if (! $last)
                        <div class="fi-sidebar-item-grouped-border-part-not-last"></div>
                    @endif

                    <div class="fi-sidebar-item-grouped-border-part"></div>
                </div>
            @endif

            <span
                @if ($sidebarCollapsible && (! $subNavigation))
                    x-show="$store.sidebar.isOpen"
                    x-transition:enter="fi-transition-enter"
                    x-transition:enter-start="fi-transition-enter-start"
                    x-transition:enter-end="fi-transition-enter-end"
                @endif
                class="fi-sidebar-item-label"
            >
                {{ $label }}
            </span>

            @if (filled($badge))
                <span
                    @if ($sidebarCollapsible && (! $subNavigation))
                        x-show="$store.sidebar.isOpen"
                        x-transition:enter="fi-transition-enter"
                        x-transition:enter-start="fi-transition-enter-start"
                        x-transition:enter-end="fi-transition-enter-end"
                    @endif
                    class="fi-sidebar-item-badge-ctn"
                >
                    <x-filament::badge
                        :color="$badgeColor"
                        :tooltip="$badgeTooltip"
                    >
                        {{ $badge }}
                    </x-filament::badge>
                </span>
            @endif
        </a>

        @if ($isPinnable && filled($navigationKey))
            <x-filament::icon-button
                color="gray"
                :icon="$isPinned ? \Filament\Support\Icons\Heroicon::Star : \Filament\Support\Icons\Heroicon::OutlinedStar"
                icon-size="sm"
                :label="$isPinned ? __('pinnable-navigation::pinnable-navigation.actions.unpin_navigation_item') : __('pinnable-navigation::pinnable-navigation.actions.pin_navigation_item')"
                wire:click.stop="togglePin('{{ $navigationKey }}')"
                wire:target="togglePin('{{ $navigationKey }}')"
                class="fi-sidebar-pin-btn"
            />
        @endif
    </div>

    @if (($active || $activeChildItems) && $childItems)
        <ul class="fi-sidebar-sub-group-items">
            @foreach ($childItems as $childItem)
                @php
                    $isChildItemChildItemsActive = $childItem->isChildItemsActive();
                    $isChildActive = (! $isChildItemChildItemsActive) && $childItem->isActive();
                    $childItemActiveIcon = $childItem->getActiveIcon();
                    $childItemBadge = $childItem->getBadge();
                    $childItemBadgeColor = $childItem->getBadgeColor();
                    $childItemBadgeTooltip = $childItem->getBadgeTooltip();
                    $childItemIcon = $childItem->getIcon();
                    $shouldChildItemOpenUrlInNewTab = $childItem->shouldOpenUrlInNewTab();
                    $childItemUrl = $childItem->getUrl();
                    $childItemExtraAttributes = $childItem->getExtraAttributeBag();
                @endphp

                @include('pinnable-navigation::sidebar.item', [
                    'active' => $isChildActive,
                    'activeChildItems' => $isChildItemChildItemsActive,
                    'activeIcon' => $childItemActiveIcon,
                    'attributes' => \Filament\Support\prepare_inherited_attributes($childItemExtraAttributes),
                    'badge' => $childItemBadge,
                    'badgeColor' => $childItemBadgeColor,
                    'badgeTooltip' => $childItemBadgeTooltip,
                    'first' => $loop->first,
                    'grouped' => true,
                    'icon' => $childItemIcon,
                    'item' => $childItem,
                    'label' => $childItem->getLabel(),
                    'last' => $loop->last,
                    'shouldOpenUrlInNewTab' => $shouldChildItemOpenUrlInNewTab,
                    'subGrouped' => true,
                    'subNavigation' => $subNavigation,
                    'url' => $childItemUrl,
                ])
            @endforeach
        </ul>
    @endif
</li>

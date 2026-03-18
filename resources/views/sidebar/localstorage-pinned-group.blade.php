@php
    $sidebarCollapsible ??= filament()->isSidebarCollapsibleOnDesktop();
    $groupIcon = config('pinnable-navigation.group_icon', 'heroicon-o-star');
@endphp

<li
    class="fi-sidebar-group fi-localstorage-pinned-group"
    data-localstorage-pinned-group="1"
    data-group-label="group:pinned"
    hidden
>
    <div
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
            {{ config('pinnable-navigation.group_title', __('pinnable-navigation::pinnable-navigation.group_label')) }}
        </span>
    </div>

    <ul
        @if ($sidebarCollapsible)
            x-show="$store.sidebar.isOpen"
            x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start"
            x-transition:enter-end="fi-transition-enter-end"
        @endif
        class="fi-sidebar-group-items"
        data-localstorage-pinned-items
    ></ul>
</li>

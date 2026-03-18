<div>
    @php
        $pinIcon = (string) config('pinnable-navigation.pin_icon', 'heroicon-o-star');
        $unpinIcon = (string) config('pinnable-navigation.unpin_icon', 'heroicon-s-star');
        $outlinedIcon = base64_encode(\Filament\Support\generate_icon_html($pinIcon)->toHtml());
        $filledIcon = base64_encode(\Filament\Support\generate_icon_html($unpinIcon)->toHtml());
    @endphp

    @if ($isVisible && filled($navigationKey))
        @if ($usesDatabase)
            <x-filament::icon-button
                color="gray"
                :icon="$isPinned ? $unpinIcon : $pinIcon"
                :label="$isPinned ? __('pinnable-navigation::pinnable-navigation.actions.unpin_from_sidebar') : __('pinnable-navigation::pinnable-navigation.actions.pin_to_sidebar')"
                wire:click="toggle"
            />
        @else
            <x-filament::icon-button
                color="gray"
                :icon="$pinIcon"
                :label="__('pinnable-navigation::pinnable-navigation.actions.pin_to_sidebar')"
                :data-localstorage-page-pin="$navigationKey"
                :data-localstorage-key="$localStorageKey"
                :data-navigation-key="$navigationKey"
                :data-pin-label="__('pinnable-navigation::pinnable-navigation.actions.pin_to_sidebar')"
                :data-unpin-label="__('pinnable-navigation::pinnable-navigation.actions.unpin_from_sidebar')"
                :data-outlined-icon="$outlinedIcon"
                :data-filled-icon="$filledIcon"
            />
        @endif
    @endif
</div>

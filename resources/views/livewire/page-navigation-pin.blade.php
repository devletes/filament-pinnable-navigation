<div>
    @if ($isVisible && filled($navigationKey))
        <x-filament::icon-button
            color="gray"
            :icon="$isPinned ? \Filament\Support\Icons\Heroicon::Star : \Filament\Support\Icons\Heroicon::OutlinedStar"
            :label="$isPinned ? __('pinnable-navigation::pinnable-navigation.actions.unpin_from_sidebar') : __('pinnable-navigation::pinnable-navigation.actions.pin_to_sidebar')"
            wire:click="toggle"
        />
    @endif
</div>

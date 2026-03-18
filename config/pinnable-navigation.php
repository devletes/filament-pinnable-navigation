<?php

/*
|--------------------------------------------------------------------------
| Filament Pinnable Navigation
|--------------------------------------------------------------------------
|
| Package: devletes/filament-pinnable-navigation
| Organization: Devletes
| Repository: https://github.com/devletes/filament-pinnable-navigation
|
*/

return [
    // Persist pinned items in the database. When enabled, run the package migration.
    'database_enabled' => false,

    // Database table used to store pinned navigation items when database persistence is enabled.
    'table_name' => 'pinned_navigation_items',

    // Label shown for the synthetic pinned navigation group.
    'group_title' => 'Pinned',

    // Optional icon shown for the pinned navigation group. Set to null to hide it.
    'group_icon' => 'heroicon-o-star',

    // Icon used for items that are not pinned yet.
    'pin_icon' => 'heroicon-o-star',

    // Icon used for items that are already pinned.
    'unpin_icon' => 'heroicon-s-star',

    // Show the page-header pin button on Filament resource index pages.
    'show_in_resource' => true,

    // Keep only one managed navigation group open at a time.
    // Disable this to fall back to Filament's default group collapsing behavior.
    'accordion_mode' => true,
];

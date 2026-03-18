# Filament Pinnable Navigation

`devletes/filament-pinnable-navigation` adds pinnable grouped sidebar navigation to Filament 5 panels.

## Requirements

- PHP `^8.2`
- Filament `^5.0`

## Installation

```bash
composer require devletes/filament-pinnable-navigation
```

Register the plugin on any panel:

```php
use Devletes\FilamentPinnableNavigation\PinnableNavigationPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->plugin(PinnableNavigationPlugin::make());
}
```

## Configuration

Publish the config file if you want to customize behavior:

```bash
php artisan vendor:publish --tag="pinnable-navigation-config"
```

Default configuration:

```php
return [
    'database_enabled' => false,
    'table_name' => 'pinned_navigation_items',
    'group_title' => 'Pinned',
    'group_icon' => 'heroicon-o-star',
    'pin_icon' => 'heroicon-o-star',
    'unpin_icon' => 'heroicon-s-star',
    'show_in_resource' => true,
    'accordion_mode' => true,
];
```

Configuration options:

- `database_enabled`: Persist pins in the database instead of browser localStorage.
- `table_name`: Database table used when database persistence is enabled.
- `group_title`: Label used for the synthetic pinned group.
- `group_icon`: Optional icon shown for the pinned group.
- `pin_icon`: Icon used when an item is not pinned.
- `unpin_icon`: Icon used when an item is already pinned.
- `show_in_resource`: Show the page-header pin toggle on Filament resource index pages.
- `accordion_mode`: Keep only one managed navigation group open at a time. Disable it to fall back to Filament's default grouped navigation behavior.

## Persistence

By default, pin state is stored in browser localStorage per panel and authenticated user. No migration is required in this mode.

If you want to persist pins in the database instead:

1. Publish the config file.
2. Set `database_enabled` to `true`.
3. Run migrations.

```bash
php artisan migrate
```

The package migration is registered automatically, so it does not need to be published separately.

## Usage

- Grouped navigation items can be pinned from the sidebar.
- When `show_in_resource` is enabled, the current resource page can also be pinned or unpinned from the page header.
- Pinned items are shown in a dedicated group at the top of the sidebar.

## Local Testing

This repository includes a Testbench workbench app for package development.

```bash
composer build
vendor\\bin\\testbench serve --host=127.0.0.1 --port=8080
```

Then open [http://127.0.0.1:8080/admin](http://127.0.0.1:8080/admin) and sign in with:

- `test@example.com`
- `password`

## Release Checklist

Before publishing a release:

1. Run `composer test`.
2. Run `vendor\\bin\\pint --test`.
3. Run `composer validate --strict`.
4. Create a semver tag such as `v1.0.0`.
5. Submit the public repository to Packagist.
6. Enable the Packagist GitHub webhook so pushes and tags sync automatically.

## License

MIT. See [LICENSE.md](LICENSE.md).

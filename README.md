# Filament Pinnable Navigation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devletes/filament-pinnable-navigation.svg?style=flat-square)](https://packagist.org/packages/devletes/filament-pinnable-navigation)
[![Total Downloads](https://img.shields.io/packagist/dt/devletes/filament-pinnable-navigation.svg?style=flat-square)](https://packagist.org/packages/devletes/filament-pinnable-navigation)
[![License](https://img.shields.io/packagist/l/devletes/filament-pinnable-navigation.svg?style=flat-square)](https://packagist.org/packages/devletes/filament-pinnable-navigation)
[![GitHub Stars](https://img.shields.io/github/stars/devletes/filament-pinnable-navigation?style=flat-square)](https://github.com/devletes/filament-pinnable-navigation/stargazers)

Allow users to dynamically pin sidebar navigation items to a pinned group.

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
    'model' => \Devletes\FilamentPinnableNavigation\Models\PinnableNavigationPin::class,
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
- `model`: Eloquent model used to store pins. Point it at your own class extending `PinnableNavigationPin` to customize storage.
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
2. Publish the package migrations.
3. Set `database_enabled` to `true`.
4. Run migrations.

```bash
php artisan vendor:publish --tag="pinnable-navigation-migrations"
php artisan migrate
```

## Usage

- Grouped navigation items can be pinned from the sidebar.
- When `show_in_resource` is enabled, the current resource page can also be pinned or unpinned from the page header.
- Pinned items are shown in a dedicated group at the top of the sidebar.

## Screenshots

### Pin navigation items from the sidebar

![Sidebar item available to pin](docs/images/none-pinned.png)

### Dedicated pinned group

![Pinned group with one item](docs/images/one-pinned.png)

### Multiple pinned items

![Pinned group with multiple items](docs/images/two-pinned.png)

## Need something custom?

We build production Filament panels and plugins for teams that want to ship fast without compromising on polish. If you need a custom feature, an extended variant of this package, or a fully bespoke component built for your stack, we can help.

- **Browse the rest of our Filament work:** [filament.devletes.com](https://filament.devletes.com)
- **Get in touch:** [salman@devletes.com](mailto:salman@devletes.com)

Typical engagements: new Filament plugins, custom resources/widgets/actions, theme + UX work, integrations with your existing services, and one-off tailored forks of our open-source packages.

## Have you seen our Orbit theme?

Orbit is our dark-first theme for Filament 5. Every panel surface is restyled — sidebar, topbar, forms, tables, modals, notifications.

<table><tr>
<td width="50%"><img src="https://filament.devletes.com/screenshots/orbit/resource_detail_with_hero_card_light.png" alt="Orbit theme, light mode"></td>
<td width="50%"><img src="https://filament.devletes.com/screenshots/orbit/resource_detail_with_hero_card.png" alt="Orbit theme, dark mode"></td>
</tr></table>

**[Check it out here](https://filament.devletes.com/orbit) →**

## Credits

- [Salman Hijazi](https://www.linkedin.com/in/syedsalmanhijazi/)

## License

MIT. See [LICENSE.md](LICENSE.md).

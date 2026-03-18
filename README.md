# Filament Pinnable Navigation

`devletes/filament-pinnable-navigation` adds a pinnable sidebar layer to Filament 5 panels.

## Requirements

- PHP 8.2+
- Laravel 11.28+ or 12.x
- Filament 5.x

## Installation

Install the package with Composer:

```bash
composer require devletes/filament-pinnable-navigation
```

Publish the config file if you need to override package defaults:

```bash
php artisan vendor:publish --tag="pinnable-navigation-config"
```

## Usage

Activate the feature on any Filament panel by registering the plugin:

```php
use Filament\Panel;
use SalmanHijazi\PinnableNavigation\PinnableNavigationPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->plugin(PinnableNavigationPlugin::make());
}
```

Once enabled, grouped navigation items get a star button in the sidebar and the current page can be pinned or unpinned from the page header actions area.

If you prefer a shorter alias, the package also provides `->pinnableNavigation()` as a convenience wrapper around the plugin registration.

## Development

Run the test suite with:

```bash
composer test
```

Format the code with:

```bash
composer format
```

Start the local workbench app with:

```bash
composer serve
```

The workbench includes a Filament admin panel at `/admin`. Visiting `/` auto-signs in the seeded `test@example.com` user and redirects to that panel. You can also sign in manually at `/admin/login` with `test@example.com` / `password`.

## Publishing checklist

- Finalize the package behavior and translations
- Verify the workbench panel behavior through `composer serve`
- Tag a release and submit the repository to Packagist

If you want to publish under a different Packagist vendor later, update the package name in `composer.json` before the first public release.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.




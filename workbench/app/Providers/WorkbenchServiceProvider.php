<?php

namespace Workbench\App\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use SalmanHijazi\PinnableNavigation\PinnableNavigationPlugin;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Panel::configureUsing(
            fn (Panel $panel): Panel => $panel->plugin(PinnableNavigationPlugin::make()),
        );
    }
}

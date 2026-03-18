<?php

namespace SalmanHijazi\PinnableNavigation\Tests\Support;

use Filament\Panel;
use SalmanHijazi\PinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationGroupedPage;
use SalmanHijazi\PinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationTopLevelPage;

class FilamentNavigationTestPanelFactory
{
    public static function make(string $id = 'test'): Panel
    {
        return Panel::make()
            ->id($id)
            ->path($id)
            ->pages([
                NavigationTopLevelPage::class,
                NavigationGroupedPage::class,
            ])
            ->navigationGroups([
                'Team',
            ]);
    }
}

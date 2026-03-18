<?php

namespace SalmanHijazi\PinnableNavigation\Tests\Fixtures\Filament\Pages;

use Filament\Pages\Page;

class NavigationTopLevelPage extends Page
{
    protected static ?int $navigationSort = -2;

    protected static ?string $navigationLabel = 'Overview';

    protected string $view = 'filament-panels::pages.page';

    public static function getNavigationUrl(): string
    {
        return '/tests/overview';
    }

    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return 'tests.overview';
    }
}

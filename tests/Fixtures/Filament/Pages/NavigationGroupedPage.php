<?php

namespace SalmanHijazi\PinnableNavigation\Tests\Fixtures\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class NavigationGroupedPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Team';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament-panels::pages.page';

    public static function getNavigationBadge(): ?string
    {
        return '7';
    }

    public static function getNavigationUrl(): string
    {
        return '/tests/reports';
    }

    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return 'tests.reports';
    }
}

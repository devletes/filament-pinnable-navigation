<?php

namespace Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Clusters;

use Filament\Clusters\Cluster;

class TestCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament-panels::pages.page';

    public static function getNavigationUrl(): string
    {
        return '/tests/settings';
    }

    public static function getNavigationItemActiveRoutePattern(): string
    {
        return 'tests.settings';
    }
}

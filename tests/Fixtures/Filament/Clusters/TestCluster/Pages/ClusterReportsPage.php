<?php

namespace Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Clusters\TestCluster\Pages;

use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Clusters\TestCluster;
use Filament\Pages\Page;

class ClusterReportsPage extends Page
{
    protected static ?string $cluster = TestCluster::class;

    protected static ?string $navigationLabel = 'Cluster Reports';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament-panels::pages.page';

    public static function getNavigationUrl(): string
    {
        return '/tests/settings/reports';
    }

    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return 'tests.settings.reports';
    }
}

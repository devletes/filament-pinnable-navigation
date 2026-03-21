<?php

namespace Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Clusters\TestCluster\Pages;

use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Clusters\TestCluster;
use Filament\Pages\Page;

class ClusterPreferencesPage extends Page
{
    protected static ?string $cluster = TestCluster::class;

    protected static ?string $navigationLabel = 'Cluster Preferences';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament-panels::pages.page';

    public static function getNavigationUrl(): string
    {
        return '/tests/settings/preferences';
    }

    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return 'tests.settings.preferences';
    }
}

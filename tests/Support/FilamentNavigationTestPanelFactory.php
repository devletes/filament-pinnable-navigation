<?php

namespace Devletes\FilamentPinnableNavigation\Tests\Support;

use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Clusters\TestCluster;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Clusters\TestCluster\Pages\ClusterPreferencesPage;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Clusters\TestCluster\Pages\ClusterReportsPage;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationGroupedPage;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationTopLevelPage;
use Filament\Panel;

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

    public static function makeWithCluster(string $id = 'test'): Panel
    {
        return static::make($id)->pages([
            TestCluster::class,
            ClusterReportsPage::class,
            ClusterPreferencesPage::class,
        ]);
    }
}

<?php

use Devletes\FilamentPinnableNavigation\Livewire\PinnableSidebar;
use Devletes\FilamentPinnableNavigation\PinnableNavigationPlugin;
use Devletes\FilamentPinnableNavigation\Support\Navigation\NavigationKeyResolver;
use Devletes\FilamentPinnableNavigation\Support\Navigation\PanelNavigationBuilder;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationGroupedPage;
use Devletes\FilamentPinnableNavigation\Tests\Support\CreatesNavigationTestTables;
use Devletes\FilamentPinnableNavigation\Tests\Support\FilamentNavigationTestPanelFactory;
use Filament\Facades\Filament;
use Workbench\App\Models\User;

uses(CreatesNavigationTestTables::class);

beforeEach(function (): void {
    config()->set('pinnable-navigation.database_enabled', true);
    $this->setUpNavigationTables();
});

it('toggles pins for the current panel', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $panel = FilamentNavigationTestPanelFactory::make('admin-test')->plugin(PinnableNavigationPlugin::make());
    Filament::setCurrentPanel($panel);
    $this->actingAs($user);

    $navigationKey = app(NavigationKeyResolver::class)->forPage(NavigationGroupedPage::class);
    $sidebar = app(PinnableSidebar::class);

    $sidebar->togglePin($navigationKey);

    $this->assertDatabaseHas('pinned_navigation_items', [
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->getKey(),
        'panel_id' => 'admin-test',
        'navigation_key' => $navigationKey,
    ]);

    $navigation = app(PanelNavigationBuilder::class)->build($panel, $user);
    expect($navigation[1]->getLabel())->toBe('Pinned');

    $sidebar->togglePin($navigationKey);

    $this->assertDatabaseMissing('pinned_navigation_items', [
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->getKey(),
        'panel_id' => 'admin-test',
        'navigation_key' => $navigationKey,
    ]);
});

it('keeps pin state isolated per panel', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $navigationKey = app(NavigationKeyResolver::class)->forPage(NavigationGroupedPage::class);

    $adminPanel = FilamentNavigationTestPanelFactory::make('admin-test')->plugin(PinnableNavigationPlugin::make());
    Filament::setCurrentPanel($adminPanel);
    $this->actingAs($user);

    app(PinnableSidebar::class)->togglePin($navigationKey);

    $employeePanel = FilamentNavigationTestPanelFactory::make('employee-test')->plugin(PinnableNavigationPlugin::make());
    Filament::setCurrentPanel($employeePanel);

    $navigation = app(PanelNavigationBuilder::class)->build($employeePanel, $user);

    expect($navigation)->toHaveCount(2)
        ->and($navigation[1]->getLabel())->toBe('Team');
});

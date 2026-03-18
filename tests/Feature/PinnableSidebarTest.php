<?php

use Devletes\FilamentPinnableNavigation\Livewire\PageNavigationPin;
use Devletes\FilamentPinnableNavigation\Livewire\PinnableSidebar;
use Devletes\FilamentPinnableNavigation\PinnableNavigationPlugin;
use Devletes\FilamentPinnableNavigation\Support\Navigation\NavigationKeyResolver;
use Devletes\FilamentPinnableNavigation\Support\Navigation\PanelNavigationBuilder;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationGroupedPage;
use Devletes\FilamentPinnableNavigation\Tests\Support\CreatesNavigationTestTables;
use Devletes\FilamentPinnableNavigation\Tests\Support\FilamentNavigationTestPanelFactory;
use Filament\Facades\Filament;
use Livewire\Livewire;
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

    $panel = FilamentNavigationTestPanelFactory::make('admin-test')
        ->userMenu(false)
        ->databaseNotifications(false)
        ->plugin(PinnableNavigationPlugin::make());
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
    expect($navigation[1]->getItems())->toHaveCount(1);

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

    expect(app(PanelNavigationBuilder::class)->build($employeePanel, $user))
        ->sequence(
            fn ($group) => $group->label->toBeNull(),
            fn ($group) => $group->label->not->toBe('Pinned'),
        );
});

it('does not render pin localstorage metadata in database mode', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $panel = FilamentNavigationTestPanelFactory::make('admin-test')
        ->userMenu(false)
        ->databaseNotifications(false)
        ->plugin(PinnableNavigationPlugin::make());
    Filament::setCurrentPanel($panel);
    $this->actingAs($user);

    $sidebarHtml = Livewire::test(PinnableSidebar::class)->html();

    Livewire::test(PageNavigationPin::class)
        ->assertSet('usesDatabase', true)
        ->assertSet('localStorageKey', null);

    expect($sidebarHtml)->toContain('data-persistence-mode="database"')
        ->toContain('const managedGroups = accordionGroups.includes(this.label)')
        ->not->toContain('data-localstorage-key=')
        ->not->toContain('data-localstorage-pinned-group="1"')
        ->not->toContain('data-localstorage-pin-button=');
});

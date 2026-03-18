<?php

use Devletes\FilamentPinnableNavigation\Livewire\PageNavigationPin;
use Devletes\FilamentPinnableNavigation\Livewire\PinnableSidebar;
use Devletes\FilamentPinnableNavigation\Support\Navigation\PanelNavigationBuilder;
use Devletes\FilamentPinnableNavigation\Support\Navigation\PinPersistenceManager;
use Devletes\FilamentPinnableNavigation\Support\Navigation\UserNavigationPinService;
use Devletes\FilamentPinnableNavigation\Tests\Support\CreatesNavigationTestTables;
use Devletes\FilamentPinnableNavigation\Tests\Support\FilamentNavigationTestPanelFactory;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Workbench\App\Filament\Resources\UserResource;
use Workbench\App\Models\User;

uses(CreatesNavigationTestTables::class);

beforeEach(function (): void {
    config()->set('pinnable-navigation.database_enabled', false);
    $this->setUpNavigationTables();
});

it('bypasses the database pin store when database persistence is disabled', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $service = app(UserNavigationPinService::class);

    expect(Schema::hasTable('pinned_navigation_items'))->toBeFalse()
        ->and($service->getPinnedKeys($user, 'admin'))->toBeEmpty()
        ->and($service->isPinned($user, 'admin', 'page:test'))->toBeFalse();

    $service->pin($user, 'admin', 'page:test');
    $service->unpin($user, 'admin', 'page:test');

    expect($service->toggle($user, 'admin', 'page:test'))->toBeFalse();
});

it('renders localstorage metadata for the sidebar and page pin', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    config()->set('pinnable-navigation.group_title', 'Bookmarks');
    config()->set('pinnable-navigation.pin_icon', 'heroicon-o-bookmark');
    config()->set('pinnable-navigation.unpin_icon', 'heroicon-s-bookmark');

    $panel = FilamentNavigationTestPanelFactory::make('admin-test')
        ->userMenu(false)
        ->databaseNotifications(false);
    Filament::setCurrentPanel($panel);
    $this->actingAs($user);

    $request = request()->create('/tests/reports', 'GET');
    Route::name('tests.reports')->get('/tests/reports', fn () => 'ok');
    $route = Route::getRoutes()->getByName('tests.reports');
    $request->setRouteResolver(fn () => $route->bind($request));
    app()->instance('request', $request);
    app()->instance('originalRequest', $request);

    $sidebarHtml = Livewire::test(PinnableSidebar::class)->html();
    $pagePinHtml = Livewire::test(PageNavigationPin::class)->html();
    $storageKey = app(PinPersistenceManager::class)->getLocalStorageKey($panel, $user);

    expect(app(PanelNavigationBuilder::class)->build($panel, $user))->toHaveCount(2)
        ->and($sidebarHtml)->toContain('data-persistence-mode="localstorage"')
        ->and($sidebarHtml)->toContain('data-localstorage-key="'.$storageKey.'"')
        ->and($sidebarHtml)->toContain('data-localstorage-pinned-group="1"')
        ->and($sidebarHtml)->toContain('Bookmarks')
        ->and($sidebarHtml)->toContain('data-accordion-enabled="1"')
        ->and($sidebarHtml)->toContain("localStorage.getItem('collapsedGroups')")
        ->and($sidebarHtml)->toContain('data-accordion-groups=')
        ->and($sidebarHtml)->toContain('Array.isArray($store.sidebar.collapsedGroups)')
        ->and($sidebarHtml)->toContain('group:team')
        ->and($sidebarHtml)->toContain('data-localstorage-pin-button="page:DevletesFilamentPinnableNavigationTestsFixturesFilamentPagesNavigationGroupedPage"')
        ->and($pagePinHtml)->toContain('data-localstorage-page-pin="page:DevletesFilamentPinnableNavigationTestsFixturesFilamentPagesNavigationGroupedPage"')
        ->and($pagePinHtml)->toContain('data-localstorage-key="'.$storageKey.'"');
});

it('falls back to default filament group behavior when accordion mode is disabled', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    config()->set('pinnable-navigation.accordion_mode', false);

    $panel = FilamentNavigationTestPanelFactory::make('admin-test')
        ->userMenu(false)
        ->databaseNotifications(false);
    Filament::setCurrentPanel($panel);
    $this->actingAs($user);

    $request = request()->create('/tests/reports', 'GET');
    Route::name('tests.reports.disabled')->get('/tests/reports', fn () => 'ok');
    $route = Route::getRoutes()->getByName('tests.reports.disabled');
    $request->setRouteResolver(fn () => $route->bind($request));
    app()->instance('request', $request);
    app()->instance('originalRequest', $request);

    $sidebarHtml = Livewire::test(PinnableSidebar::class)->html();

    expect($sidebarHtml)->toContain('data-accordion-enabled="0"')
        ->and($sidebarHtml)->toContain('$store.sidebar.toggleCollapsedGroup(this.label)')
        ->and($sidebarHtml)->toContain('data-accordion-managed="0"')
        ->and($sidebarHtml)->toContain("data-accordion-groups='[]'");
});

it('hides the resource page header pin when show_in_resource is disabled', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    config()->set('pinnable-navigation.show_in_resource', false);

    $panel = FilamentNavigationTestPanelFactory::make('admin-test')
        ->resources([
            UserResource::class,
        ]);
    Filament::setCurrentPanel($panel);
    $this->actingAs($user);

    $request = request()->create('/admin-test/users', 'GET');
    Route::name('filament.admin-test.resources.users.index')->get('/admin-test/users', fn () => 'ok');
    $route = Route::getRoutes()->getByName('filament.admin-test.resources.users.index');
    $request->setRouteResolver(fn () => $route->bind($request));
    app()->instance('request', $request);
    app()->instance('originalRequest', $request);

    $pagePinHtml = Livewire::test(PageNavigationPin::class)->html();

    expect($pagePinHtml)->not->toContain('data-localstorage-page-pin')
        ->and($pagePinHtml)->not->toContain('fi-icon-btn');
});

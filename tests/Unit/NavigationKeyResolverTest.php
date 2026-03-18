<?php

use Devletes\FilamentPinnableNavigation\Support\Navigation\NavigationKeyResolver;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationGroupedPage;
use Devletes\FilamentPinnableNavigation\Tests\Support\CreatesNavigationTestTables;
use Devletes\FilamentPinnableNavigation\Tests\Support\FilamentNavigationTestPanelFactory;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Route;

uses(CreatesNavigationTestTables::class);

beforeEach(function (): void {
    config()->set('pinnable-navigation.database_enabled', true);
    $this->setUpNavigationTables();
});

it('generates page and resource keys', function (): void {
    $resolver = app(NavigationKeyResolver::class);

    expect($resolver->forPage(NavigationGroupedPage::class))
        ->toBe('page:DevletesFilamentPinnableNavigationTestsFixturesFilamentPagesNavigationGroupedPage')
        ->and($resolver->forResource(Dashboard::class))
        ->toBe('resource:FilamentPagesDashboard');
});

it('resolves the current page key from the active route', function (): void {
    Route::name('tests.reports')->get('/tests/reports', fn () => 'ok');

    $panel = FilamentNavigationTestPanelFactory::make();
    Filament::setCurrentPanel($panel);

    $request = request()->create('/tests/reports', 'GET');
    $route = Route::getRoutes()->getByName('tests.reports');
    $request->setRouteResolver(fn () => $route->bind($request));
    app()->instance('request', $request);
    app()->instance('originalRequest', $request);

    expect(app(NavigationKeyResolver::class)->resolveCurrentPageKey($panel))
        ->toBe('page:DevletesFilamentPinnableNavigationTestsFixturesFilamentPagesNavigationGroupedPage');
});

<?php

use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Route;
use SalmanHijazi\PinnableNavigation\Support\Navigation\NavigationKeyResolver;
use SalmanHijazi\PinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationGroupedPage;
use SalmanHijazi\PinnableNavigation\Tests\Support\CreatesNavigationTestTables;
use SalmanHijazi\PinnableNavigation\Tests\Support\FilamentNavigationTestPanelFactory;

uses(CreatesNavigationTestTables::class);

beforeEach(function (): void {
    $this->setUpNavigationTables();
});

it('generates page and resource keys', function (): void {
    $resolver = app(NavigationKeyResolver::class);

    expect($resolver->forPage(NavigationGroupedPage::class))
        ->toBe('page:SalmanHijaziPinnableNavigationTestsFixturesFilamentPagesNavigationGroupedPage')
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
        ->toBe('page:SalmanHijaziPinnableNavigationTestsFixturesFilamentPagesNavigationGroupedPage');
});

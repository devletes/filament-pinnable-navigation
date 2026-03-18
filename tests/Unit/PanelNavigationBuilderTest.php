<?php

use Devletes\FilamentPinnableNavigation\Support\Navigation\NavigationKeyResolver;
use Devletes\FilamentPinnableNavigation\Support\Navigation\PanelNavigationBuilder;
use Devletes\FilamentPinnableNavigation\Support\Navigation\UserNavigationPinService;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Filament\Pages\NavigationGroupedPage;
use Devletes\FilamentPinnableNavigation\Tests\Support\CreatesNavigationTestTables;
use Devletes\FilamentPinnableNavigation\Tests\Support\FilamentNavigationTestPanelFactory;
use Workbench\App\Models\User;

uses(CreatesNavigationTestTables::class);

beforeEach(function (): void {
    config()->set('pinnable-navigation.database_enabled', true);
    $this->setUpNavigationTables();
});

it('omits the pinned group when nothing is pinned', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $panel = FilamentNavigationTestPanelFactory::make();
    $navigation = app(PanelNavigationBuilder::class)->build($panel, $user);

    expect($navigation)->toHaveCount(2)
        ->and($navigation[0]->getLabel())->toBeNull()
        ->and($navigation[1]->getLabel())->toBe('Team');
});

it('inserts the pinned group between top level items and native groups', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $panel = FilamentNavigationTestPanelFactory::make();
    $key = app(NavigationKeyResolver::class)->forPage(NavigationGroupedPage::class);

    app(UserNavigationPinService::class)->pin($user, $panel->getId(), $key);

    $navigation = app(PanelNavigationBuilder::class)->build($panel, $user);

    expect($navigation)->toHaveCount(3)
        ->and($navigation[0]->getLabel())->toBeNull()
        ->and($navigation[1]->getLabel())->toBe('Pinned')
        ->and($navigation[2]->getLabel())->toBe('Team');
});

it('clones pinned items without removing the original item or its badge', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $panel = FilamentNavigationTestPanelFactory::make();
    $key = app(NavigationKeyResolver::class)->forPage(NavigationGroupedPage::class);

    app(UserNavigationPinService::class)->pin($user, $panel->getId(), $key);

    $navigation = app(PanelNavigationBuilder::class)->build($panel, $user);

    $pinnedGroup = $navigation[1];
    $nativeGroup = $navigation[2];

    expect($pinnedGroup->getItems()[0]->getLabel())->toBe('Reports')
        ->and($nativeGroup->getItems()[0]->getLabel())->toBe('Reports')
        ->and($pinnedGroup->getItems()[0]->getBadge())->toBe('7')
        ->and($nativeGroup->getItems()[0]->getBadge())->toBe('7');
});

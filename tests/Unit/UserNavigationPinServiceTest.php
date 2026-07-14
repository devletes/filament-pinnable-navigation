<?php

use Devletes\FilamentPinnableNavigation\Support\Navigation\UserNavigationPinService;
use Devletes\FilamentPinnableNavigation\Tests\Fixtures\Models\CustomNavigationPin;
use Devletes\FilamentPinnableNavigation\Tests\Support\CreatesNavigationTestTables;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Workbench\App\Models\User;

uses(CreatesNavigationTestTables::class);

beforeEach(function (): void {
    config()->set('pinnable-navigation.database_enabled', true);
    $this->setUpNavigationTables();
});

it('writes pins idempotently and scopes them per panel', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $service = app(UserNavigationPinService::class);

    $service->pin($user, 'admin', 'page:test');
    $service->pin($user, 'admin', 'page:test');
    $service->pin($user, 'employee', 'page:test');

    $this->assertDatabaseCount('pinned_navigation_items', 2);
    expect($service->isPinned($user, 'admin', 'page:test'))->toBeTrue()
        ->and($service->isPinned($user, 'employee', 'page:test'))->toBeTrue();
});

it('only removes the matching pin when unpinning', function (): void {
    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $service = app(UserNavigationPinService::class);

    $service->pin($user, 'admin', 'page:one');
    $service->pin($user, 'admin', 'page:two');

    $service->unpin($user, 'admin', 'page:one');

    expect($service->isPinned($user, 'admin', 'page:one'))->toBeFalse()
        ->and($service->isPinned($user, 'admin', 'page:two'))->toBeTrue();

    $this->assertDatabaseCount('pinned_navigation_items', 1);
});

it('stores pins through the configured custom model class', function (): void {
    config()->set('pinnable-navigation.model', CustomNavigationPin::class);

    Schema::create('custom_pinned_navigation_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('user_type', 100);
        $table->string('panel_id', 50);
        $table->string('navigation_key', 191);
        $table->timestamps();
    });

    $user = User::query()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $service = app(UserNavigationPinService::class);

    $service->pin($user, 'admin', 'page:custom');

    $this->assertDatabaseCount('custom_pinned_navigation_items', 1);
    $this->assertDatabaseCount('pinned_navigation_items', 0);

    expect($service->isPinned($user, 'admin', 'page:custom'))->toBeTrue()
        ->and($service->getPinnedKeys($user, 'admin')->all())->toBe(['page:custom']);

    $service->unpin($user, 'admin', 'page:custom');

    $this->assertDatabaseCount('custom_pinned_navigation_items', 0);
});

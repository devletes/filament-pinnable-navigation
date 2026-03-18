<?php

namespace Devletes\FilamentPinnableNavigation\Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesNavigationTestTables
{
    protected function setUpNavigationTables(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        if (! config('pinnable-navigation.database_enabled')) {
            return;
        }

        Schema::create(config('pinnable-navigation.table_name', 'pinned_navigation_items'), function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 100);
            $table->string('panel_id', 50);
            $table->string('navigation_key', 191);
            $table->timestamps();

            $table->unique(['user_type', 'user_id', 'panel_id', 'navigation_key'], 'pinnable_nav_unique');
            $table->index(['user_type', 'user_id', 'panel_id'], 'pinnable_nav_user_panel_index');
        });
    }
}

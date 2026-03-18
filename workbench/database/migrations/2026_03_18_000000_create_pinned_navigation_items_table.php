<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists(config('pinnable-navigation.table_name', 'pinned_navigation_items'));
    }
};

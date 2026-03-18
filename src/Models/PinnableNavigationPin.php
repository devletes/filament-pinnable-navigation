<?php

namespace Devletes\FilamentPinnableNavigation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PinnableNavigationPin extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'panel_id',
        'navigation_key',
    ];

    public function getTable(): string
    {
        return (string) config('pinnable-navigation.table_name', 'pinned_navigation_items');
    }

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}

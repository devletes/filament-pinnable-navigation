<?php

namespace Devletes\FilamentPinnableNavigation\Tests\Fixtures\Models;

use Devletes\FilamentPinnableNavigation\Models\PinnableNavigationPin;

class CustomNavigationPin extends PinnableNavigationPin
{
    public function getTable(): string
    {
        return 'custom_pinned_navigation_items';
    }
}

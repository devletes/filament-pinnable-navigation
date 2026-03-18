<?php

namespace Workbench\App\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Workbench\App\Filament\Resources\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
}

<?php

namespace App\Filament\Resources\EditorialMembers\Pages;

use App\Filament\Resources\EditorialMembers\EditorialMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEditorialMembers extends ListRecords
{
    protected static string $resource = EditorialMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

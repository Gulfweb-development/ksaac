<?php

namespace App\Filament\Resources\WorkAreas\Pages;

use App\Filament\Resources\WorkAreas\WorkAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkAreas extends ListRecords
{
    protected static string $resource = WorkAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

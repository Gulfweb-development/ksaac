<?php

namespace App\Filament\Resources\JournalNews\Pages;

use App\Filament\Resources\JournalNews\JournalNewsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJournalNews extends ListRecords
{
    protected static string $resource = JournalNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

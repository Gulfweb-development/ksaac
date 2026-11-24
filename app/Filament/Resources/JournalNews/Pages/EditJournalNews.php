<?php

namespace App\Filament\Resources\JournalNews\Pages;

use App\Filament\Resources\JournalNews\JournalNewsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJournalNews extends EditRecord
{
    protected static string $resource = JournalNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

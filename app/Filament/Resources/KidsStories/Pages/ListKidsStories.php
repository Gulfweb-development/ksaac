<?php

namespace App\Filament\Resources\KidsStories\Pages;

use App\Filament\Resources\KidsStories\KidsStoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKidsStories extends ListRecords
{
    protected static string $resource = KidsStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

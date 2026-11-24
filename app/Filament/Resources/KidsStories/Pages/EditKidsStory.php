<?php

namespace App\Filament\Resources\KidsStories\Pages;

use App\Filament\Resources\KidsStories\KidsStoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKidsStory extends EditRecord
{
    protected static string $resource = KidsStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

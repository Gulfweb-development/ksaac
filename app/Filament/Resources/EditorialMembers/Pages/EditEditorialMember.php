<?php

namespace App\Filament\Resources\EditorialMembers\Pages;

use App\Filament\Resources\EditorialMembers\EditorialMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEditorialMember extends EditRecord
{
    protected static string $resource = EditorialMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

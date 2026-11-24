<?php

namespace App\Filament\Resources\EditorialMembers\Pages;

use App\Filament\Resources\EditorialMembers\EditorialMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEditorialMember extends CreateRecord
{
    protected static string $resource = EditorialMemberResource::class;
}

<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\CreateRecord;

/** For papers received by email or post; papers sent through the journal website arrive here on their own. */
class CreateSubmission extends CreateRecord
{
    protected static string $resource = SubmissionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}

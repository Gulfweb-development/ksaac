<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionActions;
use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

/** The editor's workspace for one paper: workflow buttons, paper, files, reviews and history. */
class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->reference;
    }

    protected function getHeaderActions(): array
    {
        return [
            SubmissionActions::startScreening(),
            SubmissionActions::sendToReviewers(),
            SubmissionActions::decide(),
            SubmissionActions::startRevisionRound(),
            SubmissionActions::publish(),
            SubmissionActions::recordReward(),
            ActionGroup::make([
                EditAction::make(),
                SubmissionActions::withdraw(),
            ]),
        ];
    }
}

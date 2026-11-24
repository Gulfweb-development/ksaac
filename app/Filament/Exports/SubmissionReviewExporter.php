<?php

namespace App\Filament\Exports;

use App\Models\SubmissionReview;
use Filament\Actions\Exports\ExportColumn;

/** Review assignments; confidential comments are left out on purpose. */
class SubmissionReviewExporter extends BaseExporter
{
    protected static ?string $model = SubmissionReview::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('submission.reference')->label(__('Reference')),
            ExportColumn::make('submission.title')->label(__('Title')),
            ExportColumn::make('reviewer.name')->label(__('Reviewer')),
            ExportColumn::make('round')->label(__('Round')),
            ExportColumn::make('status')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('due_on')->label(__('Due')),
            ExportColumn::make('recommendation')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('average')->label(__('Average score'))->state(fn (SubmissionReview $record) => $record->averageScore()),
            ExportColumn::make('completed_at')->label(__('Completed')),
        ];
    }
}

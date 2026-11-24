<?php

namespace App\Filament\Exports;

use App\Models\Submission;
use Filament\Actions\Exports\ExportColumn;

class SubmissionExporter extends BaseExporter
{
    protected static ?string $model = Submission::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('reference'),
            ExportColumn::make('title'),
            ExportColumn::make('researcher.name')->label(__('Researcher')),
            ExportColumn::make('researcher.email')->label(__('Email')),
            ExportColumn::make('status')->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('round')->label(__('Review round')),
            ExportColumn::make('editor.name')->label(__('Handling editor')),
            ExportColumn::make('created_at')->label(__('Received on')),
            ExportColumn::make('decided_at')->label(__('Decision date')),
            ExportColumn::make('issue.number')->label(__('Published in issue')),
            ExportColumn::make('reward_amount')->label(__('Reward (KWD)')),
            ExportColumn::make('reward_paid_on')->label(__('Reward paid on')),
        ];
    }
}

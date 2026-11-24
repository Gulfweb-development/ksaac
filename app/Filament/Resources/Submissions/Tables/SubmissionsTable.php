<?php

namespace App\Filament\Resources\Submissions\Tables;

use App\Enums\ReviewStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Exports\SubmissionExporter;
use App\Filament\Support\Filters;
use App\Filament\Support\TableExports;
use App\Models\Admin;
use App\Models\Submission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubmissionsTable
{
    public static function configure(Table $table): Table
    {
        $live = fn (Builder $q) => $q->whereColumn('submission_reviews.round', 'submissions.round')->where('submission_reviews.status', '!=', ReviewStatus::Declined->value);

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['researcher', 'editor'])->withCount([
                'reviews as reviews_total' => $live,
                'reviews as reviews_done' => fn (Builder $q) => $live($q)->where('submission_reviews.status', ReviewStatus::Completed->value),
            ]))
            ->columns([
                TextColumn::make('reference')->searchable()->sortable()->weight('bold')->copyable(),
                TextColumn::make('title')->searchable()->limit(70)->wrap()->description(fn (Submission $r) => $r->researcher?->name)->extraAttributes(['dir' => 'auto']),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('reviews_progress')
                    ->label('Reviews')
                    ->state(fn (Submission $r) => $r->reviews_total ? $r->reviews_done.' / '.$r->reviews_total : '—')
                    ->description(fn (Submission $r) => $r->round > 1 ? __('Round :n', ['n' => $r->round]) : null),
                TextColumn::make('editor.name')->label('Handling editor')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Received on')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(SubmissionStatus::class)->multiple(),
                Filter::make('awaiting_decision')
                    ->label('All reviews in, awaiting decision')
                    ->query(fn (Builder $query) => $query->where('status', SubmissionStatus::UnderReview)
                        ->whereHas('reviews', $live)
                        ->whereDoesntHave('reviews', fn (Builder $r) => $live($r)->where('submission_reviews.status', '!=', ReviewStatus::Completed->value))),
                SelectFilter::make('editor_id')->label('Handling editor')->options(fn () => Admin::orderBy('name')->pluck('name', 'id')),
                SelectFilter::make('language')->label('Paper language')->options(config('app.locale_labels')),
                TernaryFilter::make('reward_paid_on')->label('Reward paid')->nullable(),
                Filters::dateRange('created_at', __('Received on')),
            ])
            ->headerActions([
                TableExports::header(SubmissionExporter::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...TableExports::bulk(SubmissionExporter::class),
                ]),
            ]);
    }
}

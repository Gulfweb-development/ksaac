<?php

namespace App\Filament\Resources\Submissions\RelationManagers;

use App\Enums\ReviewStatus;
use App\Filament\Exports\SubmissionReviewExporter;
use App\Filament\Support\TableExports;
use App\Models\SubmissionReview;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Editors' view of every review on the paper, all rounds; reviewers are added with "Send to reviewers". */
class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Reviews');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('round', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('reviewer'))
            ->columns([
                TextColumn::make('reviewer.name')->label('Reviewer'),
                TextColumn::make('round')->label('Round'),
                TextColumn::make('status')->badge(),
                TextColumn::make('due_on')->label('Due')->date()->color(fn (SubmissionReview $r) => $r->isOverdue() ? 'danger' : null)
                    ->description(fn (SubmissionReview $r) => $r->isOverdue() ? __('Overdue') : null),
                TextColumn::make('recommendation')->badge()->placeholder('—'),
                TextColumn::make('average')->label('Average score')->state(fn (SubmissionReview $r) => $r->averageScore())->placeholder('—'),
                TextColumn::make('completed_at')->label('Completed')->date()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->options(ReviewStatus::class),
                SelectFilter::make('round')->label('Round')->options(fn () => $this->getOwnerRecord()->reviews()->distinct()->orderBy('round')->pluck('round', 'round')),
            ])
            ->headerActions([
                TableExports::header(SubmissionReviewExporter::class),
            ])
            ->recordActions([
                Action::make('report')
                    ->label(__('Read report'))
                    ->icon('heroicon-o-document-text')
                    ->visible(fn (SubmissionReview $r) => $r->status === ReviewStatus::Completed)
                    ->modalHeading(fn (SubmissionReview $r) => __('Review by :name', ['name' => $r->reviewer->name]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close'))
                    ->schema(fn (SubmissionReview $r) => [
                        Grid::make(4)->schema(collect(SubmissionReview::CRITERIA)->map(fn ($c) => TextEntry::make("scores.$c")->label(__(str($c)->ucfirst()->toString()))->state($r->scores[$c] ?? '—'))->all()),
                        TextEntry::make('recommendation')->state($r->recommendation)->badge(),
                        TextEntry::make('comments_to_author')->label('Comments to the author')->state($r->comments_to_author)->placeholder('—')->extraAttributes(['dir' => 'auto']),
                        TextEntry::make('comments_to_editor')->label('Confidential comments to the editor')->state($r->comments_to_editor)->placeholder('—')->extraAttributes(['dir' => 'auto']),
                    ]),
                DeleteAction::make()->label(__('Remove'))->visible(fn (SubmissionReview $r) => $r->status !== ReviewStatus::Completed),
            ]);
    }
}

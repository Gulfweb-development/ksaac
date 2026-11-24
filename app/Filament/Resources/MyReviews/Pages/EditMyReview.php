<?php

namespace App\Filament\Resources\MyReviews\Pages;

use App\Enums\ReviewRecommendation;
use App\Enums\ReviewStatus;
use App\Filament\Resources\MyReviews\MyReviewResource;
use App\Models\SubmissionReview;
use App\Services\Journal\PeerReview;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** The reviewer reads the anonymised paper and fills in the review form. */
class EditMyReview extends EditRecord
{
    protected static string $resource = MyReviewResource::class;

    public function getTitle(): string
    {
        return __('Review of :reference', ['reference' => $this->getRecord()->submission->reference]);
    }

    private function isOpen(): bool
    {
        return in_array($this->getRecord()->status, [ReviewStatus::Invited, ReviewStatus::Accepted]);
    }

    public function form(Schema $schema): Schema
    {
        $scores = collect(SubmissionReview::CRITERIA)->map(fn (string $criterion) => ToggleButtons::make("scores.$criterion")
            ->label(__(str($criterion)->ucfirst()->toString()))
            ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
            ->inline()
            ->required())->all();

        return $schema
            ->columns(5)
            ->disabled(fn () => ! $this->isOpen())
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Paper'))
                        ->description(__('Blind review: the authors are not shown to you, and your name is not shown to them.'))
                        ->schema([
                            TextEntry::make('paper_title')->label('Title')->state(fn (SubmissionReview $record) => $record->submission->title)->weight('bold')->extraAttributes(['dir' => 'auto']),
                            TextEntry::make('paper_abstract')->label('Abstract')->state(fn (SubmissionReview $record) => $record->submission->abstract)->prose()->extraAttributes(['dir' => 'auto']),
                            TextEntry::make('paper_keywords')->label('Keywords')->state(fn (SubmissionReview $record) => $record->submission->keywords)->placeholder('—')->extraAttributes(['dir' => 'auto']),
                            TextEntry::make('paper_files')
                                ->label('Manuscript')
                                ->state(fn (SubmissionReview $record) => $record->submission->getMedia('blind_manuscript')
                                    ->map(fn ($media) => '<a class="underline" target="_blank" href="'.e($media->getTemporaryUrl(now()->addMinutes(30))).'">'.e($media->file_name).'</a>')
                                    ->join('<br>') ?: null)
                                ->html()
                                ->placeholder(__('No file yet; please contact the editor.')),
                            TextEntry::make('due_on')->label('Due')->date()->placeholder('—'),
                        ]),
                ]),
                Group::make()->columnSpan(3)->schema([
                    Section::make(__('Your review'))->columns(2)->schema([
                        ...$scores,
                        Radio::make('recommendation')->options(ReviewRecommendation::class)->required()->columnSpanFull(),
                        Textarea::make('comments_to_author')
                            ->label('Comments to the author')
                            ->helperText(__('Sent to the researcher without your name.'))
                            ->rows(8)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('comments_to_editor')
                            ->label('Confidential comments to the editor')
                            ->helperText(__('Only the editors see this.'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('accept')
                ->label(__('Accept invitation'))
                ->icon(Heroicon::OutlinedHandThumbUp)
                ->color('success')
                ->visible(fn () => $this->getRecord()->status === ReviewStatus::Invited)
                ->action(fn () => $this->respond(true)),
            Action::make('decline')
                ->label(__('Decline'))
                ->icon(Heroicon::OutlinedHandThumbDown)
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->getRecord()->status === ReviewStatus::Invited)
                ->action(fn () => $this->respond(false)),
        ];
    }

    protected function getFormActions(): array
    {
        if (! $this->isOpen()) {
            return [];
        }

        return [
            $this->getSaveFormAction()
                ->label(__('Submit review'))
                ->requiresConfirmation()
                ->modalDescription(__('You cannot change the review after submitting it.')),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            app(PeerReview::class)->complete($record, $data);
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['data.recommendation' => $e->getMessage()]);
        }

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('Thank you — your review has been sent to the editors.');
    }

    protected function getRedirectUrl(): string
    {
        return MyReviewResource::getUrl();
    }

    private function respond(bool $accept): void
    {
        app(PeerReview::class)->respond($this->getRecord(), $accept);
        Notification::make()->success()->title($accept ? __('Invitation accepted') : __('Invitation declined'))->send();

        $accept ? $this->refreshFormData(['status']) : $this->redirect(MyReviewResource::getUrl());
    }
}

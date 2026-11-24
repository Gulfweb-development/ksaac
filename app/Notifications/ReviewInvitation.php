<?php

namespace App\Notifications;

use App\Filament\Resources\MyReviews\MyReviewResource;
use App\Models\SubmissionReview;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to a reviewer (admin account) when a submission is assigned to them. Blind: no author details. */
class ReviewInvitation extends Notification
{
    use Queueable;

    public function __construct(public SubmissionReview $review) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $submission = $this->review->submission;

        return (new MailMessage)
            ->subject(__('Review invitation: :reference', ['reference' => $submission->reference]))
            ->greeting(__('Dear :name,', ['name' => $notifiable->name]))
            ->line(__('The Arab Childhood Journal invites you to review the following manuscript:'))
            ->line('“'.$submission->title.'”')
            ->when($this->review->due_on, fn (MailMessage $m) => $m->line(__('Please send your review by :date.', ['date' => $this->review->due_on->format('Y-m-d')])))
            ->action(__('Open the review'), MyReviewResource::getUrl('edit', ['record' => $this->review]))
            ->line(__('You can accept or decline the invitation from the same page.'));
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('New review invitation'))
            ->body($this->review->submission->reference.' — '.str($this->review->submission->title)->limit(80))
            ->actions([Action::make('open')->label(__('Open the review'))->url(MyReviewResource::getUrl('edit', ['record' => $this->review]))])
            ->getDatabaseMessage();
    }
}

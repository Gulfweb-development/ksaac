<?php

namespace App\Notifications;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Emails the researcher whenever their submission changes status. Comments to the author are included; reviewer names never are. */
class SubmissionUpdate extends Notification
{
    use Queueable;

    public function __construct(public Submission $submission, public ?string $comments = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->submission;

        $message = match ($s->status) {
            SubmissionStatus::Received => __('We have received your research and will check it shortly.'),
            SubmissionStatus::Screening => __('Your research is going through the editors\' initial check.'),
            SubmissionStatus::UnderReview => __('Your research has been sent to peer reviewers.'),
            SubmissionStatus::RevisionsRequested => __('The reviewers ask for revisions. Please read the comments below and send a revised version.'),
            SubmissionStatus::Accepted => __('Congratulations: your research has been accepted for publication.'),
            SubmissionStatus::Rejected => __('After review, the journal is unable to publish your research.'),
            SubmissionStatus::Published => __('Your research has been published in issue :issue.', ['issue' => $s->issue?->number]),
            SubmissionStatus::Withdrawn => __('Your submission has been withdrawn.'),
        };

        return (new MailMessage)
            ->subject(__('Your submission :reference: :status', ['reference' => $s->reference, 'status' => $s->status->getLabel()]))
            ->greeting(__('Dear :name,', ['name' => $notifiable->name ?? __('researcher')]))
            ->line('“'.$s->title.'”')
            ->line($message)
            ->when(filled($this->comments), fn (MailMessage $m) => $m->line(__('Comments:'))->line($this->comments))
            ->salutation(__('Journal of Arab Children'));
    }
}

<?php

namespace App\Notifications;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Tells the subscriber whether their bank-transfer subscription was approved or rejected. */
class SubscriptionDecision extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription, public bool $newAccount = false) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->subscription;
        $mail = (new MailMessage)->greeting(__('Dear :name,', ['name' => $s->name]))->salutation(__('Journal of Arab Children'));

        if ($s->status === SubscriptionStatus::Rejected) {
            return $mail
                ->subject(__('Your journal subscription request'))
                ->line(__('We could not approve your subscription request.'))
                ->when(filled($s->admin_note), fn (MailMessage $m) => $m->line($s->admin_note))
                ->line(__('Please contact us if you have any questions.'));
        }

        return $mail
            ->subject(__('Your journal subscription is active'))
            ->line(__('Your subscription (:plan) has been approved.', ['plan' => $s->plan->getLabel()]))
            ->when($s->ends_on, fn (MailMessage $m) => $m->line(__('It is valid until :date.', ['date' => $s->ends_on->format('Y-m-d')])))
            ->when($s->issue, fn (MailMessage $m) => $m->line(__('You can read issue :issue.', ['issue' => $s->issue->number])))
            // shortcut: the journal site's "Forgot password" page arrives with the front end (Phase 6); until then new subscribers cannot sign in.
            ->when($this->newAccount, fn (MailMessage $m) => $m->line(__('An account was created for :email. Use "Forgot password" on the journal website to set your password.', ['email' => $s->email])));
    }
}

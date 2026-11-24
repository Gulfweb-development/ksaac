<?php

namespace App\Services\Journal;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Admin;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionDecision;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Approves or rejects subscription requests sent with a bank-transfer receipt.
 * shortcut: payment is manual (bank transfer + receipt); an online gateway would call approve() after a successful charge.
 */
class SubscriptionDesk
{
    /** Approve: link or create the reader's account, set the access period, email them. */
    public function approve(Subscription $subscription, CarbonInterface $startsOn, ?int $issueId = null, ?Admin $by = null): User
    {
        $this->requirePending($subscription);

        if ($subscription->plan === SubscriptionPlan::SingleIssue && ! ($issueId ?? $subscription->issue_id)) {
            throw new DomainException(__('Choose the issue this single-issue purchase is for.'));
        }

        [$user, $created] = DB::transaction(function () use ($subscription, $startsOn, $issueId, $by) {
            $user = $subscription->user ?? User::firstWhere('email', $subscription->email);
            $created = ! $user;

            $user ??= User::create([
                'name' => $subscription->name,
                'email' => $subscription->email,
                'mobile' => $subscription->phone,
                // Reader sets their own password via "Forgot password"; nobody knows this one.
                'password' => Str::password(32),
            ]);

            $isYearly = $subscription->plan !== SubscriptionPlan::SingleIssue;

            $subscription->update([
                'user_id' => $user->id,
                'status' => SubscriptionStatus::Approved,
                'starts_on' => $startsOn,
                'ends_on' => $isYearly ? $startsOn->copy()->addYear()->subDay() : null,
                'issue_id' => $isYearly ? null : ($issueId ?? $subscription->issue_id),
                'reviewed_by' => $by?->getKey(),
                'reviewed_at' => now(),
            ]);

            return [$user, $created];
        });

        $user->notify(new SubscriptionDecision($subscription->refresh(), newAccount: $created));

        return $user;
    }

    public function reject(Subscription $subscription, string $reason, ?Admin $by = null): void
    {
        $this->requirePending($subscription);

        $subscription->update([
            'status' => SubscriptionStatus::Rejected,
            'admin_note' => $reason,
            'reviewed_by' => $by?->getKey(),
            'reviewed_at' => now(),
        ]);

        Notification::route('mail', $subscription->email)->notify(new SubscriptionDecision($subscription));
    }

    private function requirePending(Subscription $subscription): void
    {
        if ($subscription->status !== SubscriptionStatus::Pending) {
            throw new DomainException(__('This request has already been handled.'));
        }
    }
}

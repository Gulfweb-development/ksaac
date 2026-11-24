<?php

namespace App\Filament\Resources\Subscriptions;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Issue;
use App\Models\Subscription;
use App\Services\Journal\SubscriptionDesk;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

/** Approve / Reject, used on the table rows and on the edit page. */
class SubscriptionActions
{
    public static function approve(): Action
    {
        return Action::make('approve')
            ->label(__('Approve'))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Subscription $record) => $record->status === SubscriptionStatus::Pending)
            ->modalDescription(__('Creates the reader\'s account if needed and emails them.'))
            ->schema(fn (Subscription $record) => [
                DatePicker::make('starts_on')->label('Start date')->default(today())->required(),
                Select::make('issue_id')
                    ->label('Issue')
                    ->options(Issue::orderByDesc('number')->get()->mapWithKeys(fn (Issue $i) => [$i->id => __('Issue').' '.$i->number.' — '.$i->title]))
                    ->default($record->issue_id)
                    ->searchable()
                    ->required()
                    ->visible($record->plan === SubscriptionPlan::SingleIssue),
            ])
            ->action(function (Subscription $record, array $data) {
                self::run(fn () => app(SubscriptionDesk::class)->approve($record, Carbon::parse($data['starts_on']), $data['issue_id'] ?? null, auth('admin')->user()), __('Subscription approved'));
            });
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->label(__('Reject'))
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Subscription $record) => $record->status === SubscriptionStatus::Pending)
            ->schema([
                Textarea::make('reason')->label('Reason (sent to the subscriber)')->required()->rows(3)->maxLength(1000),
            ])
            ->action(function (Subscription $record, array $data) {
                self::run(fn () => app(SubscriptionDesk::class)->reject($record, $data['reason'], auth('admin')->user()), __('Subscription rejected'));
            });
    }

    private static function run(callable $step, string $success): void
    {
        try {
            $step();
            Notification::make()->success()->title($success)->send();
        } catch (DomainException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }
    }
}

<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionActions;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SubscriptionActions::approve()->after(fn () => $this->refreshFormData(['status', 'starts_on', 'ends_on'])),
            SubscriptionActions::reject()->after(fn () => $this->refreshFormData(['status', 'admin_note'])),
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

/** Lets staff record a request received by phone or email; it then goes through Approve like any other. */
class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;
}

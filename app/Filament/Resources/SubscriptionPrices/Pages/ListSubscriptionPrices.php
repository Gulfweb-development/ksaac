<?php

namespace App\Filament\Resources\SubscriptionPrices\Pages;

use App\Filament\Resources\SubscriptionPrices\SubscriptionPriceResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionPrices extends ListRecords
{
    protected static string $resource = SubscriptionPriceResource::class;

    public function getSubheading(): ?string
    {
        return __('Edit a price directly in the table; it saves when you leave the field.');
    }
}

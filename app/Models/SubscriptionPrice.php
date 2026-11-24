<?php

namespace App\Models;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionRegion;
use Illuminate\Database\Eloquent\Model;

/** One cell of the journal's price grid: plan × region. */
class SubscriptionPrice extends Model
{
    protected $fillable = ['plan', 'region', 'amount', 'currency'];

    protected function casts(): array
    {
        return [
            'plan' => SubscriptionPlan::class,
            'region' => SubscriptionRegion::class,
            'amount' => 'decimal:3',
        ];
    }
}

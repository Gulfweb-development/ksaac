<?php

namespace App\Models;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionRegion;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A journal subscription: starts as a bank-transfer request (with receipt) from the subscription form,
 * then an editor approves (account + dates) or rejects it.
 */
class Subscription extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    // New requests are pending in memory too, not only after a reload from the database.
    protected $attributes = ['status' => 'pending', 'is_renewal' => false];

    protected $fillable = ['user_id', 'is_renewal', 'name', 'email', 'phone', 'address', 'plan', 'region', 'amount', 'currency', 'issue_id', 'status', 'starts_on', 'ends_on', 'admin_note', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return [
            'is_renewal' => 'boolean',
            'plan' => SubscriptionPlan::class,
            'region' => SubscriptionRegion::class,
            'status' => SubscriptionStatus::class,
            'amount' => 'decimal:3',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    /** Approved and not past its end date (single-issue purchases have no end date). */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', SubscriptionStatus::Approved)
            ->where(fn (Builder $q) => $q->whereNull('ends_on')->orWhereDate('ends_on', '>=', today()));
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Approved && (! $this->ends_on || $this->ends_on->gte(today()));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('receipt')->singleFile()->useDisk('local');
    }
}

<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A research paper sent to the journal. Media: 'manuscript' (with author names, every version kept),
 * 'blind_manuscript' (anonymised copy reviewers see), 'attachments'.
 */
class Submission extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $fillable = ['reference', 'user_id', 'title', 'language', 'abstract', 'keywords', 'theme', 'co_authors', 'status', 'round', 'editor_id', 'decision_note', 'decided_at', 'issue_id', 'article_id', 'reward_amount', 'reward_paid_on'];

    protected $attributes = ['status' => 'received', 'round' => 1];

    protected function casts(): array
    {
        return [
            'status' => SubmissionStatus::class,
            // [{name, affiliation, email}, ...]
            'co_authors' => 'array',
            'round' => 'integer',
            'decided_at' => 'datetime',
            'reward_amount' => 'decimal:3',
            'reward_paid_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Submission $submission) {
            $submission->reference ??= static::nextReference();
        });
    }

    /** JAC-2026-0001, numbered per year. */
    public static function nextReference(): string
    {
        $prefix = 'JAC-'.now()->year.'-';
        $last = static::where('reference', 'like', $prefix.'%')->max('reference');

        return $prefix.str_pad((string) ((int) substr((string) $last, -4) + 1), 4, '0', STR_PAD_LEFT);
    }

    public function researcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'editor_id');
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SubmissionReview::class);
    }

    public function currentReviews(): HasMany
    {
        return $this->reviews()->where('round', $this->round)->where('status', '!=', ReviewStatus::Declined);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'round'])->logOnlyDirty()->dontSubmitEmptyLogs()->useLogName('submission');
    }

    public function registerMediaCollections(): void
    {
        // Private disk: unpublished research and receipts must never have a public URL.
        $this->addMediaCollection('manuscript')->useDisk('local');
        $this->addMediaCollection('blind_manuscript')->useDisk('local');
        $this->addMediaCollection('attachments')->useDisk('local');
    }
}

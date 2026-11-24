<?php

namespace App\Models;

use App\Enums\ReviewRecommendation;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One reviewer's assignment on one review round of a submission. */
class SubmissionReview extends Model
{
    use HasFactory;

    /** Score criteria on the review form, each rated 1–5. */
    public const CRITERIA = ['originality', 'methodology', 'clarity', 'contribution'];

    protected $fillable = ['submission_id', 'reviewer_id', 'round', 'status', 'due_on', 'responded_at', 'completed_at', 'scores', 'recommendation', 'comments_to_author', 'comments_to_editor'];

    protected $attributes = ['status' => 'invited'];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'recommendation' => ReviewRecommendation::class,
            'scores' => 'array',
            'round' => 'integer',
            'due_on' => 'date',
            'responded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewer_id');
    }

    public function averageScore(): ?float
    {
        $scores = array_filter((array) $this->scores, 'is_numeric');

        return $scores ? round(array_sum($scores) / count($scores), 1) : null;
    }

    public function isOverdue(): bool
    {
        return $this->due_on && $this->due_on->isPast() && in_array($this->status, [ReviewStatus::Invited, ReviewStatus::Accepted]);
    }
}

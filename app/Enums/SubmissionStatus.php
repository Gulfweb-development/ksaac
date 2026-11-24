<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Where a research submission is in the journal's peer-review process. */
enum SubmissionStatus: string implements HasColor, HasLabel
{
    case Received = 'received';
    case Screening = 'screening';
    case UnderReview = 'under_review';
    case RevisionsRequested = 'revisions_requested';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Published = 'published';
    case Withdrawn = 'withdrawn';

    public function getLabel(): string
    {
        return match ($this) {
            self::Received => __('Received'),
            self::Screening => __('Initial check'),
            self::UnderReview => __('Under review'),
            self::RevisionsRequested => __('Revisions requested'),
            self::Accepted => __('Accepted'),
            self::Rejected => __('Rejected'),
            self::Published => __('Published'),
            self::Withdrawn => __('Withdrawn'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Received, self::Screening => 'gray',
            self::UnderReview => 'info',
            self::RevisionsRequested => 'warning',
            self::Accepted, self::Published => 'success',
            self::Rejected, self::Withdrawn => 'danger',
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Rejected, self::Published, self::Withdrawn]);
    }
}

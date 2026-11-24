<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReviewRecommendation: string implements HasColor, HasLabel
{
    case Accept = 'accept';
    case MinorRevisions = 'minor_revisions';
    case MajorRevisions = 'major_revisions';
    case Reject = 'reject';

    public function getLabel(): string
    {
        return match ($this) {
            self::Accept => __('Accept as is'),
            self::MinorRevisions => __('Accept with minor revisions'),
            self::MajorRevisions => __('Major revisions needed'),
            self::Reject => __('Reject'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Accept, self::MinorRevisions => 'success',
            self::MajorRevisions => 'warning',
            self::Reject => 'danger',
        };
    }

    /** +1 leans accept, -1 leans reject, 0 undecided. Two reviewers on opposite sides means a third is needed. */
    public function leaning(): int
    {
        return match ($this) {
            self::Accept, self::MinorRevisions => 1,
            self::MajorRevisions => 0,
            self::Reject => -1,
        };
    }
}

<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Contribution types accepted by the journal (from its publishing rules). */
enum ArticleType: string implements HasLabel
{
    case FieldResearch = 'field_research';
    case TheoreticalStudy = 'theoretical_study';
    case BookReview = 'book_review';
    case ConferenceReport = 'conference_report';
    case Article = 'article';

    public function getLabel(): string
    {
        return match ($this) {
            self::FieldResearch => __('Field or experimental research'),
            self::TheoreticalStudy => __('Theoretical study'),
            self::BookReview => __('Book review'),
            self::ConferenceReport => __('Conference report'),
            self::Article => __('General article'),
        };
    }
}

<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SubscriptionPlan: string implements HasLabel
{
    case SingleIssue = 'single_issue';
    case YearlyIndividual = 'yearly_individual';
    case YearlyInstitution = 'yearly_institution';

    public function getLabel(): string
    {
        return match ($this) {
            self::SingleIssue => __('Single issue (individual)'),
            self::YearlyIndividual => __('Yearly subscription (individual)'),
            self::YearlyInstitution => __('Yearly subscription (institution)'),
        };
    }
}

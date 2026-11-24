<?php

namespace App\Filament\Resources\NewsletterSubscribers\Schemas;

use App\Enums\Site;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site')
                    ->options(Site::class)
                    ->default(Site::Ksaac)
                    ->required()
                    ->live(),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    // One subscription per email per site.
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, $get) => $rule->where('site', $get('site') instanceof Site ? $get('site')->value : $get('site'))),
                Select::make('locale')
                    ->options(config('app.locale_labels'))
                    ->default('ar')
                    ->required(),
                DateTimePicker::make('unsubscribed_at')
                    ->helperText(__('Leave empty while the person is subscribed.')),
            ]);
    }
}

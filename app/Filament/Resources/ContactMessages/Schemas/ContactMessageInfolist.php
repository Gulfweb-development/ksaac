<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make(__('Message'))
                    ->columnSpan(2)
                    ->schema([
                        // dir=auto: messages arrive in Arabic or English regardless of the admin's language.
                        TextEntry::make('subject')->placeholder('—')->weight('bold')->extraAttributes(['dir' => 'auto']),
                        TextEntry::make('message')->prose()->formatStateUsing(fn (string $state) => nl2br(e($state)))->html()->extraAttributes(['dir' => 'auto']),
                    ]),
                Section::make(__('Sender'))
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('name')->extraAttributes(['dir' => 'auto']),
                        TextEntry::make('email')->copyable()->url(fn ($record) => 'mailto:'.$record->email),
                        TextEntry::make('phone')->placeholder('—')->copyable(),
                        TextEntry::make('site')->badge(),
                        TextEntry::make('created_at')->label('Received at')->dateTime(),
                    ]),
            ]);
    }
}

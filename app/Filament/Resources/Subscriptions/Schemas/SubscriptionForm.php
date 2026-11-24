<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionRegion;
use App\Enums\SubscriptionStatus;
use App\Models\Issue;
use App\Models\SubscriptionPrice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        // Picking plan + region fills the price from the journal's price grid.
        $fillPrice = function (Get $get, Set $set) {
            $region = $get('region');
            $price = SubscriptionPrice::where(['plan' => $get('plan'), 'region' => $region])->first();

            if ($price) {
                $set('amount', $price->amount);
                $set('currency', $price->currency);
            }
        };

        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(2)->schema([
                    Section::make(__('Subscriber'))->columns(2)->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255),
                        TextInput::make('phone')->tel()->required()->maxLength(40),
                        Toggle::make('is_renewal')->label('Renewal')->inline(false),
                        Textarea::make('address')->rows(2)->maxLength(500)->columnSpanFull(),
                    ]),
                    Section::make(__('Subscription'))->columns(2)->schema([
                        Select::make('plan')->options(SubscriptionPlan::class)->required()->live()->afterStateUpdated($fillPrice),
                        Select::make('region')->label('Region')->options(SubscriptionRegion::class)->required()->live()->afterStateUpdated($fillPrice),
                        TextInput::make('amount')->label('Price')->numeric()->required()->minValue(0),
                        TextInput::make('currency')->required()->maxLength(3),
                        Select::make('issue_id')
                            ->label('Issue')
                            ->helperText(__('For single-issue purchases.'))
                            ->options(fn () => Issue::orderByDesc('number')->get()->mapWithKeys(fn (Issue $i) => [$i->id => __('Issue').' '.$i->number.' — '.$i->title]))
                            ->searchable()
                            ->visible(fn (Get $get) => in_array($get('plan'), [SubscriptionPlan::SingleIssue, SubscriptionPlan::SingleIssue->value], true)),
                    ]),
                ]),
                Group::make()->columnSpan(1)->schema([
                    Section::make(__('Payment'))->schema([
                        SpatieMediaLibraryFileUpload::make('receipt')
                            ->label('Bank transfer receipt')
                            ->collection('receipt')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->openable()
                            ->disk('local')
                            ->visibility('private'),
                    ]),
                    Section::make(__('Status'))->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->placeholder(SubscriptionStatus::Pending->getLabel()),
                        DatePicker::make('starts_on')->label('Start date')->visibleOn('edit'),
                        DatePicker::make('ends_on')->label('End date')->afterOrEqual('starts_on')->visibleOn('edit')
                            ->helperText(__('Change to extend or shorten an approved subscription.')),
                        Textarea::make('admin_note')->label('Internal note')->rows(3)->maxLength(1000),
                    ]),
                ]),
            ]);
    }
}

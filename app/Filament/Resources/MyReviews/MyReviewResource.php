<?php

namespace App\Filament\Resources\MyReviews;

use App\Enums\NavigationGroup;
use App\Enums\ReviewStatus;
use App\Filament\Exports\SubmissionReviewExporter;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MyReviews\Pages\EditMyReview;
use App\Filament\Resources\MyReviews\Pages\ListMyReviews;
use App\Filament\Support\Filters;
use App\Filament\Support\TableExports;
use App\Models\SubmissionReview;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * A reviewer's own assignments. Blind review: nothing here exposes the researcher —
 * only the title, abstract, keywords and the anonymised manuscript.
 */
class MyReviewResource extends BaseResource
{
    protected static ?string $model = SubmissionReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Jac;

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'my-reviews';

    protected static function generateDefaultLabel(): string
    {
        return 'My Review';
    }

    /** Every query (list, edit, route binding) is limited to the signed-in reviewer. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('reviewer_id', auth('admin')->id())->with('submission');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $open = static::getEloquentQuery()->whereIn('status', [ReviewStatus::Invited, ReviewStatus::Accepted])->count();

        return $open ? (string) $open : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('Reviews waiting for you');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('submission.reference')->label('Reference')->searchable(),
                TextColumn::make('submission.title')->label('Title')->searchable()->limit(70)->wrap()->extraAttributes(['dir' => 'auto']),
                TextColumn::make('round')->label('Round'),
                TextColumn::make('status')->badge(),
                TextColumn::make('due_on')->label('Due')->date()->sortable()
                    ->color(fn (SubmissionReview $r) => $r->isOverdue() ? 'danger' : null),
                TextColumn::make('recommendation')->badge()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->options(ReviewStatus::class),
                Filters::dateRange('due_on', __('Due')),
            ])
            ->headerActions([
                TableExports::header(SubmissionReviewExporter::class),
            ])
            ->recordActions([
                EditAction::make()->label(__('Open')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyReviews::route('/'),
            'edit' => EditMyReview::route('/{record}'),
        ];
    }
}

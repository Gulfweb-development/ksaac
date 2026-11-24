<?php

namespace App\Providers;

use Amzad\FilamentTranslatableGroup\TranslatableGroup;
use App\Policies\ActivityPolicy;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Translatable\Facades\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override Cache::rememberForever to handle missing cache table during migrations
        $this->app->booting(function () {
            if (! Schema::hasTable('cache')) {
                // Temporarily use array cache during migrations
                config(['cache.default' => 'array']);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityPolicy::class);

        // A field filled in only one language (e.g. Arabic-only names) shows that language instead of nothing.
        Translatable::fallback(fallbackAny: true);

        Column::configureUsing(function (Column $column): void {
            $column->translateLabel();
        });

        Field::configureUsing(function (Field $field): void {
            $field->translateLabel();
        });

        BaseFilter::configureUsing(function (BaseFilter $filter): void {
            $filter->translateLabel();
        });

        // Exports belong to Admin (admin guard), not User.
        Export::polymorphicUserRelationship();

        ExportColumn::configureUsing(function (ExportColumn $column): void {
            $column->label(fn (ExportColumn $column): string => __((string) str($column->getName())->afterLast('.')->replace('_', ' ')->ucfirst()));
        });

        // Defaults for every admin table; export actions are added per table via TableExports.
        Table::configureUsing(function (Table $table): void {
            $table
                ->persistFiltersInSession()
                ->persistSearchInSession()
                ->persistSortInSession()
                ->striped()
                ->paginated([10, 25, 50, 100]);
        });

        Tab::configureUsing(function (Tab $filter): void {
            $filter->translateLabel();
        });

        Blade::directive('setting', function ($expression) {
            return "<?php echo setting({$expression}); ?>";
        });

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(config('app.supported_locales'))
                ->displayLocale(config('app.locale'))
                ->flags([
                    'ar' => 'https://img.icons8.com/color/48/kuwait.png',
                    'en' => 'https://www.svgrepo.com/show/405648/flag-for-flag-us-outlying-islands.svg',
                ]);
        });

        TranslatableGroup::configureUsing(function (TranslatableGroup $component) {
            $component
                ->localesLabels(config('app.locale_labels'))
                ->locales(config('app.supported_locales'))
                ->addDirectionByLocale()
                // Arabic + English copies sit side by side across the whole row.
                ->columnSpanFull();
        });

        Gate::define('viewApiDocs', function ($user = null) {
            return auth('admin')->check();
        });
    }
}

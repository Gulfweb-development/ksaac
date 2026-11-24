<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Language\LanguageToFont;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * One-line export setup for every admin table: XLSX/CSV (queued, Filament native) + PDF (mPDF, Arabic-safe).
 * All three formats read columns from the same Exporter class.
 */
class TableExports
{
    // shortcut: PDF renders synchronously in the request; above this, users are pointed to XLSX. Queue it if large PDFs are needed.
    public const PDF_MAX_ROWS = 3000;

    private const FORMATS = [ExportFormat::Xlsx, ExportFormat::Csv];

    // App default disk is 'public'; export files hold personal data and must not be web-reachable.
    private const DISK = 'local';

    /** @param class-string<Exporter> $exporter */
    public static function header(string $exporter): ActionGroup
    {
        return ActionGroup::make([
            ExportAction::make()->exporter($exporter)->formats(self::FORMATS)->fileDisk(self::DISK)->label(__('Excel / CSV')),
            Action::make('exportPdf')
                ->label(__('PDF'))
                ->icon(Heroicon::OutlinedDocumentText)
                ->action(fn (HasTable $livewire) => self::pdf(
                    $exporter,
                    $exporter::modifyQuery($livewire->getTableQueryForExport())->limit(self::PDF_MAX_ROWS + 1)->get(),
                    collect($livewire->getTable()->getFilterIndicators())
                        ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                        ->whenEmpty(fn ($c) => $c->push(__('All records')))
                        ->join(', '),
                )),
        ])
            ->label(__('Export'))
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->button();
    }

    /** @param class-string<Exporter> $exporter */
    public static function bulk(string $exporter): array
    {
        return [
            ExportBulkAction::make()->exporter($exporter)->formats(self::FORMATS)->fileDisk(self::DISK)->label(__('Export Excel / CSV')),
            BulkAction::make('exportPdf')
                ->label(__('Export PDF'))
                ->icon(Heroicon::OutlinedDocumentText)
                ->deselectRecordsAfterCompletion()
                ->action(fn (Collection $records) => self::pdf($exporter, $records, __('Selected rows'))),
        ];
    }

    /** @param class-string<Exporter> $exporter */
    public static function pdf(string $exporter, Collection $records, ?string $scope = null): ?StreamedResponse
    {
        if ($records->count() > self::PDF_MAX_ROWS) {
            Notification::make()->warning()
                ->title(__('Too many rows for PDF'))
                ->body(__('PDF is limited to :max rows. Filter the table or use Excel / CSV.', ['max' => self::PDF_MAX_ROWS]))
                ->send();

            return null;
        }

        $columns = $exporter::getVisibleColumns();
        $columnMap = collect($columns)->mapWithKeys(fn ($c) => [$c->getName() => $c->getLabel()])->all();
        $row = new $exporter(new Export(['exporter' => $exporter]), $columnMap, []);

        $model = $exporter::getModel();
        $title = __(Str::plural(Str::headline(class_basename($model))));
        $rtl = app()->getLocale() === 'ar';

        $logo = setting('general.logo');
        $font = $rtl ? 'dejavusans' : 'readexpro';

        $html = view('exports.pdf', [
            'title' => $title,
            'headings' => array_values($columnMap),
            'rows' => $records->values()->map(fn ($record) => $row($record)),
            'rtl' => $rtl,
            'font' => $font,
            'scope' => $scope ?? __('All records'),
            'generatedBy' => auth('admin')->user()?->name ?? config('app.name'),
            'generatedAt' => now()->translatedFormat('j F Y  H:i'),
            'logo' => $logo && is_file(storage_path('app/public/'.$logo)) ? storage_path('app/public/'.$logo) : public_path('images/ksaac-logo.png'),
            'swoosh' => public_path('images/pdf-swoosh.svg'),
            'website' => 'ksaac.org',
            'contact' => collect([setting('contact.mobile'), setting('contact.email')])->filter()->join('   |   '),
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => count($columnMap) > 5 ? 'A4-L' : 'A4',
            'tempDir' => storage_path('app/mpdf'),
            // Readex Pro (the website's typeface) for Latin text. mPDF cannot shape Readex's Arabic
            // (its OpenType tables crash mPDF's shaper), so Arabic runs use DejaVu Sans instead.
            'fontDir' => [...(new ConfigVariables)->getDefaults()['fontDir'], resource_path('fonts/readex-pro')],
            'fontdata' => (new FontVariables)->getDefaults()['fontdata'] + [
                'readexpro' => ['R' => 'ReadexPro-Regular.ttf', 'B' => 'ReadexPro-Bold.ttf'],
            ],
            'default_font' => $font,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'languageToFont' => new class extends LanguageToFont
            {
                public function getLanguageOptions($llcc, $adobeCJK)
                {
                    [$coreSuitable, $font] = parent::getLanguageOptions($llcc, $adobeCJK);

                    return [$coreSuitable, $font === 'xbriyaz' ? 'dejavusans' : $font];
                }
            },
            'autoArabic' => true,
        ]);
        $mpdf->SetTitle($title);
        $mpdf->SetAuthor('Kuwaiti Society for the Advancement of Arab Childhood');
        $mpdf->SetDirectionality($rtl ? 'rtl' : 'ltr');
        $mpdf->WriteHTML($html);

        $filename = Str::slug(class_basename($model)).'-'.now()->format('Y-m-d-His').'.pdf';

        return response()->streamDownload(fn () => print ($mpdf->Output('', 'S')), $filename, ['Content-Type' => 'application/pdf']);
    }
}

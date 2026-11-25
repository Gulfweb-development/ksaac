<?php

namespace Database\Seeders;

use App\Enums\Site;
use App\Models\MenuItem;
use App\Models\Partner;
use App\Models\Stat;
use App\Models\WorkArea;
use Illuminate\Database\Seeder;

/**
 * Starter KSAAC content taken from the approved homepage design (menu, stats, areas, partners).
 * Skips any table that already has rows, so it is safe to re-run.
 * php artisan db:seed --class=KsaacContentSeeder
 */
class KsaacContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->menu();
        $this->ordered(Stat::class, [
            ['value' => 1980, 'animate' => false, 'label' => ['ar' => 'سنة التأسيس', 'en' => 'Founded']],
            ['value' => 46, 'label' => ['ar' => 'عاماً من العطاء', 'en' => 'Years of work']],
            ['value' => 100, 'suffix' => '+', 'label' => ['ar' => 'مطبوعة', 'en' => 'Publications']],
            ['value' => 20, 'suffix' => '+', 'label' => ['ar' => 'مؤتمراً', 'en' => 'Conferences']],
            ['value' => 10, 'suffix' => '+', 'label' => ['ar' => 'مشاريع', 'en' => 'Projects']],
        ]);
        $this->ordered(WorkArea::class, [
            ['title' => ['ar' => 'البحوث والدراسات', 'en' => 'Research and studies'], 'description' => ['ar' => 'دعم البحوث الجديدة في تنمية الطفولة المبكرة والتربية.', 'en' => 'Supporting new research in early childhood development and education.']],
            ['title' => ['ar' => 'المطبوعات والنشر', 'en' => 'Publications'], 'description' => ['ar' => 'إصدار الكتب والدراسات والمواد التحليلية والتشخيصية والتقنية.', 'en' => 'Books, studies, and analytical, diagnostic and technical materials.']],
            ['title' => ['ar' => 'المؤتمرات والندوات', 'en' => 'Conferences and seminars'], 'description' => ['ar' => 'لقاءات علمية تجمع الباحثين والتربويين من العالم العربي.', 'en' => 'Scholarly meetings for researchers and educators across the Arab world.']],
            ['title' => ['ar' => 'مجلة الطفولة العربية', 'en' => 'Journal of Arab Children'], 'description' => ['ar' => 'مجلة علمية محكّمة تنشر البحوث في الطفولة والتربية.', 'en' => 'A peer-reviewed journal publishing research on childhood and education.']],
        ]);
        // Publication categories come from the old site: php artisan import:live ksaac --only=publications
        $this->partners();
    }

    private function ordered(string $model, array $rows): void
    {
        if ($model::exists()) {
            return;
        }

        foreach ($rows as $i => $row) {
            $model::create($row + ['sort' => $i + 1]);
        }
    }

    private function menu(): void
    {
        if (MenuItem::where('site', Site::Ksaac)->exists()) {
            return;
        }

        $tree = [
            [['ar' => 'الرئيسية', 'en' => 'Home'], '/'],
            [['ar' => 'عن الجمعية', 'en' => 'About Us'], null, [
                [['ar' => 'الجمعية', 'en' => 'The Society'], null, [
                    [['ar' => 'نبذة عن الجمعية', 'en' => 'Who we are'], '/about'],
                    [['ar' => 'مجلس الإدارة', 'en' => 'Board of directors'], '/board'],
                    [['ar' => 'الأهداف والرسالة', 'en' => 'Goals and mission'], '/mission'],
                ]],
                [['ar' => 'تواصل', 'en' => 'Reach us'], null, [
                    [['ar' => 'مركز المعلومات', 'en' => 'Information Center'], '/information-center'],
                    [['ar' => 'إتصل بنا', 'en' => 'Contact Us'], '/contact'],
                ]],
            ]],
            [['ar' => 'مطبوعات الجمعية', 'en' => 'Publications'], '/publications', [
                [['ar' => 'المطبوعات', 'en' => 'Publications'], null, [
                    [['ar' => 'الكتب والدراسات', 'en' => 'Books and studies'], '/publications/books-and-studies'],
                    [['ar' => 'الكتاب السنوي', 'en' => 'Yearbook'], '/publications/yearbook'],
                    [['ar' => 'قصص الأطفال', 'en' => 'Children’s stories'], '/publications/childrens-stories'],
                ]],
            ]],
            [['ar' => 'مشاريع الجمعية', 'en' => 'Projects'], '/projects'],
            [['ar' => 'المؤتمرات', 'en' => 'Conferences'], '/conferences'],
            [['ar' => 'مركز المعلومات', 'en' => 'Information Center'], '/information-center'],
            [['ar' => 'ركن الأطفال', 'en' => 'Kids Corner'], '/kids', [], ['ar' => 'قصص', 'en' => 'Stories']],
            [['ar' => 'مجلة الطفولة العربية', 'en' => 'Journal of Arab Children'], 'https://jac-kw.org', [], null, true],
            [['ar' => 'إتصل بنا', 'en' => 'Contact Us'], '/contact'],
        ];

        $this->createMenu($tree, null);
    }

    private function createMenu(array $items, ?int $parentId): void
    {
        foreach ($items as $i => $item) {
            [$label, $url, $children, $badge, $newTab] = array_pad($item, 5, null);

            $menuItem = MenuItem::create([
                'site' => Site::Ksaac,
                'parent_id' => $parentId,
                'label' => $label,
                'url' => $url,
                'badge' => $badge,
                'open_in_new_tab' => (bool) $newTab,
                'sort' => $i + 1,
            ]);

            $this->createMenu($children ?? [], $menuItem->id);
        }
    }

    private function partners(): void
    {
        if (Partner::exists()) {
            return;
        }

        $partners = [
            ['moe.png', 'وزارة التربية — دولة الكويت', 'Ministry of Education — Kuwait'],
            ['ku.svg', 'جامعة الكويت', 'Kuwait University'],
            ['sba.svg', 'هيئة الشارقة للكتاب', 'Sharjah Book Authority'],
            ['ipa.png', 'الاتحاد الدولي للناشرين', 'International Publishers Association'],
            ['ibby.svg', 'المجلس الدولي لكتب اليافعين', 'International Board on Books for Young People (IBBY)'],
            ['shamaa.png', 'شبكة المعلومات العربية التربوية (شمعة)', 'Arab Educational Information Network (Shamaa)'],
            ['reggio.svg', 'ريجيو تشلدرن — ريجيو إميليا', 'Reggio Children — Reggio Emilia'],
            ['jac.png', 'مجلة الطفولة العربية', 'Journal of Arab Children'],
        ];

        foreach ($partners as $i => [$file, $ar, $en]) {
            Partner::create(['name' => ['ar' => $ar, 'en' => $en], 'sort' => $i + 1])
                ->addMedia(database_path("seeders/assets/partners/$file"))
                ->preservingOriginal()
                ->toMediaCollection('logo');
        }
    }
}

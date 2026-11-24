<?php

namespace Database\Seeders;

use App\Enums\EditorialGroup;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionRegion;
use App\Models\EditorialMember;
use App\Models\SubscriptionPrice;
use Illuminate\Database\Seeder;

/**
 * Starter journal content copied from jac-kw.org: the subscription price grid and the editorial board.
 * Board names are Arabic only on the live site; English names can be added in the admin.
 * Skips any table that already has rows, so it is safe to re-run.
 * php artisan db:seed --class=JournalContentSeeder
 */
class JournalContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->prices();
        $this->editorialBoard();
    }

    private function prices(): void
    {
        if (SubscriptionPrice::exists()) {
            return;
        }

        $grid = [
            SubscriptionPlan::SingleIssue->value => [1, 1, 2],
            SubscriptionPlan::YearlyIndividual->value => [3, 4, 15],
            SubscriptionPlan::YearlyInstitution->value => [15, 15, 60],
        ];

        foreach ($grid as $plan => $amounts) {
            foreach (SubscriptionRegion::cases() as $i => $region) {
                SubscriptionPrice::create(['plan' => $plan, 'region' => $region, 'amount' => $amounts[$i], 'currency' => $region->currency()]);
            }
        }
    }

    private function editorialBoard(): void
    {
        if (EditorialMember::exists()) {
            return;
        }

        // [name, role, date of death (null = alive, '' = deceased, date unknown)]
        $board = [
            EditorialGroup::Trustees->value => [
                ['الدكتورة سعاد الصباح', 'الرئيس'],
                ['الدكتور حسن الإبراهيم', 'نائب الرئيس'],
                ['أ.د. فائزة الخرافي'],
                ['أ.د. محمد الرميحي'],
                ['الدكتور فهد محمد الراشد'],
                ['السيد عبد اللطيف الحمد'],
                ['السيد سعد علي الناهض'],
                ['د. أنور عبد الله النوري', null, '2013-08-02'],
                ['السيد محمد علي النقي'],
                ['د. عادل عيسى اليوسفي'],
                ['السيد قتيبة يوسف الغانم'],
            ],
            EditorialGroup::Advisory->value => [
                ['أ.د. حامد عمار', null, '2014-12-09'],
                ['أ.د. أسامة الخولي', null, ''],
                ['أ.د. رجاء أبو علام'],
                ['أ.د. الغالي أحرشاو'],
                ['د. عبد الله عمر العمر'],
                ['د. عبد الرحمن المصيقر'],
                ['د. بهية الجشي'],
                ['الأستاذة سعاد الرفاعي'],
            ],
            EditorialGroup::Editorial->value => [
                ['د. حسن الإبراهيم', 'رئيس هيئة التحرير'],
                ['د. علي عاشور الجعفر', 'مدير التحرير'],
                ['أ.د. قاسم الصراف', 'نائب مدير التحرير'],
                ['أ.د. بدر عمر العمر'],
                ['أ.د. محمد جواد رضا', null, '2012-05-08'],
                ['أ.د. محمد الرميحي'],
                ['د. عدنان شهاب الدين'],
                ['د. أنور عبد الله النوري', null, '2013-08-02'],
                ['د. بدر عثمان مال الله'],
                ['أ.د. فوزية عباس هادي'],
            ],
        ];

        foreach ($board as $group => $members) {
            foreach ($members as $i => $member) {
                [$name, $role, $died] = array_pad($member, 3, null);

                EditorialMember::create([
                    'group' => $group,
                    'name' => ['ar' => $name, 'en' => ''],
                    'role' => $role ? ['ar' => $role, 'en' => ''] : null,
                    'is_deceased' => $died !== null,
                    'deceased_on' => $died ?: null,
                    'sort' => $i + 1,
                ]);
            }
        }
    }
}

<?php

namespace App\Support\Import;

/**
 * Reads Arabic number words as written in the journal's issue and volume labels,
 * e.g. "العدد المائة وثمانية" → 108, "المجلد الثامن والعشرون" → 28, "العدد الحادي عشر" → 11, "العدد الصفري" → 0.
 * Works by summing the value of each word, which is how these compound numbers are built.
 */
class ArabicNumber
{
    private const WORDS = [
        'صفري' => 0, 'صفر' => 0,
        'واحد' => 1, 'أول' => 1, 'اول' => 1, 'حادي' => 1, 'احد' => 1,
        'ثاني' => 2, 'ثانى' => 2, 'اثنان' => 2, 'اثنين' => 2,
        'ثالث' => 3, 'تالث' => 3, 'ثلاثة' => 3,
        'رابع' => 4, 'أربعة' => 4, 'اربعة' => 4,
        'خامس' => 5, 'خمسة' => 5,
        'سادس' => 6, 'ستة' => 6,
        'سابع' => 7, 'سبعة' => 7,
        'ثامن' => 8, 'ثمانية' => 8,
        'تاسع' => 9, 'تسعة' => 9,
        'عاشر' => 10, 'عشرة' => 10, 'عشر' => 10,
        'عشرون' => 20, 'عشرين' => 20,
        'ثلاثون' => 30, 'ثلاثين' => 30,
        'أربعون' => 40, 'اربعون' => 40, 'أربعين' => 40,
        'خمسون' => 50, 'خمسين' => 50,
        'ستون' => 60, 'ستين' => 60,
        'سبعون' => 70, 'سبعين' => 70,
        'ثمانون' => 80, 'ثمانين' => 80,
        'تسعون' => 90, 'تسعين' => 90,
        'مائة' => 100, 'مئة' => 100, 'مائه' => 100,
    ];

    private const IGNORED = ['العدد', 'عدد', 'المجلد', 'مجلد'];

    public static function parse(string $text): ?int
    {
        if (preg_match('/\d+/', self::westernDigits($text), $m)) {
            return (int) $m[0];
        }

        $total = null;
        // "العددالخامس" (no space) → "العدد الخامس"
        $text = preg_replace('/^(العدد|المجلد)(?=\S)/u', '$1 ', trim($text));

        foreach (preg_split('/[\s\x{00A0}]+/u', $text) as $token) {
            if (in_array($token, self::IGNORED, true) || $token === '') {
                continue;
            }

            $value = self::word($token);

            if ($value === null) {
                return null; // unknown word: refuse rather than guess
            }

            $total = ($total ?? 0) + $value;
        }

        return $total;
    }

    private static function word(string $token): ?int
    {
        static $words;
        $words ??= collect(self::WORDS)->mapWithKeys(fn ($value, $word) => [self::normalize($word) => $value])->all();
        $token = self::normalize($token);

        // Strip the conjunction "و" and the article "ال" in either order ("والعشرون", "الثاني").
        foreach ([$token, preg_replace('/^و/u', '', $token)] as $candidate) {
            foreach ([$candidate, preg_replace('/^ال/u', '', $candidate)] as $bare) {
                if (array_key_exists($bare, $words)) {
                    return $words[$bare];
                }
            }
        }

        return $token === 'و' ? 0 : null;
    }

    /** Spelling variants seen on the old site: "الحادى" (ى for ي), "الاربعين" (no hamza). */
    private static function normalize(string $word): string
    {
        return strtr($word, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ي']);
    }

    private static function westernDigits(string $text): string
    {
        return strtr($text, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    }
}

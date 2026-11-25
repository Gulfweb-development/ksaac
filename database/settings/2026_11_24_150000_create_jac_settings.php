<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/** Defaults are the details published on jac-kw.org (contact page and subscription form). */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('jac.name', ['ar' => 'مجلة الطفولة العربية', 'en' => 'Journal of Arab Children']);
        $this->migrator->add('jac.logo');
        $this->migrator->add('jac.phone', '+965 24748250 / 24748479 / 24748387');
        $this->migrator->add('jac.fax', '+965 24749381');
        $this->migrator->add('jac.email', 'info@jac-kw.org');
        $this->migrator->add('jac.address', ['ar' => 'أبرق خيطان، قطعة 5، شارع 49 عبدالله بن المقفع', 'en' => 'Abraq Khaitan, Block 5, Street 49, Abdullah bin Al-Muqaffa']);
        $this->migrator->add('jac.po_box', ['ar' => 'ص.ب. 23928 الصفاة، الكويت 13100', 'en' => 'P.O. Box 23928, Safat 13100, Kuwait']);
        $this->migrator->add('jac.ios_app_url');
        $this->migrator->add('jac.android_app_url', 'https://play.google.com/store/apps/details?id=appinventor.ai_hypermatter.JAC');
        $this->migrator->add('jac.footer_about', [
            'ar' => 'مجلة علمية بحثية فصلية محكمة تصدرها الجمعية الكويتية لتقدم الطفولة العربية بدعم من مؤسسة الكويت للتقدم العلمي.',
            'en' => 'A quarterly peer-reviewed research journal published by the Kuwaiti Society for the Advancement of Arab Childhood, supported by the Kuwait Foundation for the Advancement of Sciences.',
        ]);
        $this->migrator->add('jac.copyright', ['ar' => '© مجلة الطفولة العربية — جميع الحقوق محفوظة', 'en' => '© Journal of Arab Children — All rights reserved']);
        $this->migrator->add('jac.bank_name', ['ar' => 'البنك التجاري الكويتي — الفرع الرئيسي', 'en' => 'Commercial Bank of Kuwait — Main Office']);
        $this->migrator->add('jac.account_holder', ['ar' => 'الجمعية الكويتية لتقدم الطفولة العربية', 'en' => 'Kuwait Society for Advancement of Arab Children']);
        $this->migrator->add('jac.bank_accounts', [
            ['currency' => 'KWD', 'account_number' => '0396922100414012', 'swift' => 'COMBKWKW', 'iban' => 'KW27 COMB 0000 0103 9692 2100 4140 12'],
            ['currency' => 'USD', 'account_number' => '0396922100840013', 'swift' => 'COMBKWKW', 'iban' => 'KW66 COMB 0000 0103 9692 2100 8400 13'],
        ]);
    }
};

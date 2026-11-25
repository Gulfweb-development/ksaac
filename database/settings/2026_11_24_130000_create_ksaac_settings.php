<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/** Defaults are the approved homepage design copy, so the admin opens pre-filled. */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('ksaac.org_name', ['ar' => 'الجمعية الكويتية لتقدم الطفولة العربية', 'en' => 'Kuwaiti Society for the Advancement of Arab Childhood']);
        $this->migrator->add('ksaac.tagline', ['ar' => 'منذ 1980', 'en' => 'Since 1980']);
        $this->migrator->add('ksaac.logo');
        $this->migrator->add('ksaac.phone');
        $this->migrator->add('ksaac.email');
        $this->migrator->add('ksaac.address', ['ar' => '', 'en' => '']);
        $this->migrator->add('ksaac.ios_app_url');
        $this->migrator->add('ksaac.android_app_url');
        $this->migrator->add('ksaac.footer_about', [
            'ar' => 'جمعية نفع عام تأسست في الكويت عام 1980 لتقدم المعارف الخاصة بالطفولة المبكرة والتربية في العالم العربي.',
            'en' => 'A public-benefit society founded in Kuwait in 1980 to advance knowledge on early childhood and education in the Arab world.',
        ]);
        $this->migrator->add('ksaac.copyright', [
            'ar' => '© الجمعية الكويتية لتقدم الطفولة العربية — جميع الحقوق محفوظة',
            'en' => '© Kuwaiti Society for the Advancement of Arab Childhood — All rights reserved',
        ]);

        $s = fn (string $key, array $kicker, array $title, array $subtitle = ['ar' => '', 'en' => '']) => compact('key', 'kicker', 'title', 'subtitle') + ['visible' => true];
        $this->migrator->add('homepage.sections', [
            $s('hero', ['ar' => 'الجمعية الكويتية لتقدم الطفولة العربية', 'en' => 'Kuwaiti Society for the Advancement of Arab Childhood'], ['ar' => 'نحو طفولة عربية أفضل منذ 1980', 'en' => 'Advancing Arab Childhood since 1980'], ['ar' => 'نعمل على تقدم المعارف الخاصة بتطوير الطفولة المبكرة والتربية في العالم العربي.', 'en' => 'We advance knowledge on early childhood development and education across the Arab world.']),
            $s('stats', ['ar' => '', 'en' => ''], ['ar' => '', 'en' => '']),
            $s('about', ['ar' => 'معلومات عنا', 'en' => 'About us'], ['ar' => 'جمعية نفع عام في خدمة الطفولة العربية', 'en' => 'A public-benefit society serving Arab childhood'], ['ar' => 'مجالات عمل الجمعية', 'en' => 'Areas of work']),
            $s('news', ['ar' => 'أحدث الأخبار', 'en' => 'Recent news'], ['ar' => 'من أخبار الجمعية', 'en' => 'News from the Society'], ['ar' => 'مشاركات الجمعية ولقاءاتها ومناسبات تكريم روّاد الطفولة العربية.', 'en' => 'The Society’s participation, meetings and honours for pioneers of Arab childhood.']),
            $s('publications', ['ar' => 'أحدث المطبوعات', 'en' => 'Recent publications'], ['ar' => 'مطبوعات الجمعية', 'en' => 'Publications'], ['ar' => 'كتب ودراسات محكّمة في التربية والطفولة.', 'en' => 'Books and peer-reviewed studies on education and childhood.']),
            $s('journal', ['ar' => 'مجلة الطفولة العربية', 'en' => 'Journal of Arab Children'], ['ar' => 'مجلة الطفولة العربية', 'en' => 'Journal of Arab Children']),
            $s('conferences', ['ar' => 'المؤتمرات', 'en' => 'Conferences'], ['ar' => 'ملتقيات تجمع الباحثين والمربين', 'en' => 'Gatherings for researchers and educators']),
            $s('project', ['ar' => 'أحدث المشاريع', 'en' => 'Recent project'], ['ar' => '', 'en' => '']),
            $s('kids', ['ar' => 'ركن الأطفال', 'en' => 'Kids Corner'], ['ar' => 'قصص مصوّرة لأطفالنا', 'en' => 'Illustrated stories for our children'], ['ar' => 'قصص عربية مصوّرة من إصدارات الجمعية، تُقرأ مع الأسرة وتحفظ ذاكرة المكان.', 'en' => 'Arabic picture books from the Society, made for reading together and keeping local memory alive.']),
            $s('partners', ['ar' => 'شركاؤنا', 'en' => 'Our partners'], ['ar' => 'الشركاء والجهات المتعاونة', 'en' => 'Partners and collaborators'], ['ar' => 'جهات محلية وعربية ودولية تتعاون معها الجمعية في البحوث والنشر والمؤتمرات.', 'en' => 'Local, Arab and international bodies the Society works with on research, publishing and conferences.']),
            $s('newsletter', ['ar' => '', 'en' => ''], ['ar' => 'الإشتراك في المراسلات', 'en' => 'Newsletter'], ['ar' => 'قم بتسجيل بريدك الإلكتروني لتحصل على آخر إصدارات المطبوعات', 'en' => 'Register your email to receive our latest publications']),
        ]);

        $this->migrator->add('homepage.about_body', [
            'ar' => 'تأسست الجمعية عام 1980 كجمعية نفع عام، تعمل على تقدم المعارف الخاصة بتطوير الطفولة المبكرة والتربية في العالم العربي، ودعم البحوث الجديدة، وإنتاج المواد التحليلية والتشخيصية والتقنية.',
            'en' => 'Founded in 1980 as a public-benefit society, KSAAC advances knowledge on early childhood development and education in the Arab world, supports new research, and produces analytical, diagnostic and technical materials.',
        ]);
        $this->migrator->add('homepage.about_quote', [
            'ar' => 'نعمل على تقدم المعارف الخاصة بتطوير الطفولة المبكرة والتربية في العالم العربي.',
            'en' => 'We advance knowledge on early childhood development and education across the Arab world.',
        ]);
        $this->migrator->add('homepage.about_quote_by', ['ar' => 'رسالة الجمعية', 'en' => 'Our mission']);
        $this->migrator->add('homepage.about_button_label', ['ar' => 'تعرف على الجمعية', 'en' => 'About the Society']);
        $this->migrator->add('homepage.about_button_url', '/about');
        $this->migrator->add('homepage.about_images', []);

        $this->migrator->add('homepage.journal_body', [
            'ar' => 'مجلة علمية بحثية فصلية محكّمة تقدّم للقارئ المهتم بمجال الطفولة العربية دراسات وبحوثاً علمية رصينة وقراءات عامة يستفيد منها المختصون والمهتمون، وتبرز نشاطات الجمعية ومشاريعها العلمية والتربوية.',
            'en' => 'A quarterly, peer-reviewed research journal offering readers interested in Arab childhood rigorous studies, research and general readings for specialists and the wider public, and highlighting the Society\'s scientific and educational projects.',
        ]);
        $this->migrator->add('homepage.journal_button_label', ['ar' => 'تصفح المجلة', 'en' => 'Browse the journal']);
        $this->migrator->add('homepage.journal_url', 'https://jac-kw.org');
        $this->migrator->add('homepage.journal_image');
        $this->migrator->add('homepage.journal_tags', [
            ['ar' => 'بحوث ودراسات علمية', 'en' => 'Scientific research & studies'],
            ['ar' => 'قضايا الطفولة العربية', 'en' => 'Arab childhood issues'],
            ['ar' => 'التربية والنمو المبكر', 'en' => 'Early education & growth'],
        ]);
    }
};

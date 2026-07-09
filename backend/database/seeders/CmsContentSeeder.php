<?php

namespace Database\Seeders;

use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\CMS\Infrastructure\Models\CmsMenu;
use App\Modules\CMS\Infrastructure\Models\CmsMenuItem;
use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\CMS\Infrastructure\Models\CmsSection;
use Illuminate\Database\Seeder;

class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompanyPage('home', 'home', 'الرئيسية', 'Home', 'Abnaa Abu Qasaa Trading', [
            ['section_type' => 'hero', 'title_en' => 'Abnaa Abu Qasaa Trading', 'body_en' => 'One umbrella platform for oils, dates, import/export, and real estate.', 'button_label_en' => 'Explore business units', 'button_url' => '/business-units'],
            ['section_type' => 'text', 'title_en' => 'Umbrella brand intro', 'body_en' => 'We manage specialized business units through a shared operational platform.'],
            ['section_type' => 'business_units', 'title_en' => 'Business units overview', 'body_en' => 'Explore our active business lines.'],
            ['section_type' => 'contact_cta', 'title_en' => 'Start a conversation', 'body_en' => 'Send an inquiry and our team will route it to the right unit.', 'button_label_en' => 'Contact us', 'button_url' => '/contact'],
        ]);

        $this->seedCompanyPage('about', 'about', 'عن الشركة', 'About', 'About Abnaa Abu Qasaa Trading', [
            ['section_type' => 'hero', 'title_en' => 'About Abnaa Abu Qasaa Trading', 'body_en' => 'A growing umbrella company built around focused business units.'],
            ['section_type' => 'text', 'title_en' => 'How we work', 'body_en' => 'Each business unit keeps its own focus while sharing a common platform foundation.'],
        ]);

        $this->seedCompanyPage('contact', 'contact', 'تواصل معنا', 'Contact', 'Contact Abnaa Abu Qasaa Trading', [
            ['section_type' => 'hero', 'title_en' => 'Contact us', 'body_en' => 'Send an inquiry to the umbrella company or a specific business unit.'],
            ['section_type' => 'contact_cta', 'title_en' => 'We are listening', 'body_en' => 'Use the form below and we will follow up.'],
        ]);

        $this->seedCompanyPage('business-units', 'standard', 'وحدات الأعمال', 'Business Units', 'Active business units managed by Abnaa Abu Qasaa Trading.', [
            ['section_type' => 'hero', 'title_en' => 'Business Units', 'body_en' => 'Explore the active business units managed by Abnaa Abu Qasaa Trading.'],
            ['section_type' => 'text', 'title_en' => 'Shared platform, focused units', 'body_en' => 'Each business unit keeps its operational focus while using a shared foundation for identity, CMS, settings, and future modules.'],
        ]);

        foreach (BusinessUnit::query()->where('status', 'active')->get() as $businessUnit) {
            $message = match ($businessUnit->type) {
                'product_store' => 'Product store content foundation coming soon.',
                'wholesale_store' => 'Wholesale content foundation coming soon.',
                'services_rfq' => 'Services and RFQ content foundation coming soon.',
                'real_estate' => 'Real estate content foundation coming soon.',
                default => 'Business unit content foundation coming soon.',
            };

            $page = CmsPage::query()->updateOrCreate(
                ['business_unit_id' => $businessUnit->id, 'slug' => $businessUnit->slug],
                [
                    'title_ar' => $businessUnit->name_ar,
                    'title_en' => $businessUnit->name_en,
                    'page_type' => 'business_unit_landing',
                    'status' => 'published',
                    'excerpt_en' => $message,
                    'seo_title_en' => $businessUnit->name_en,
                    'seo_description_en' => $message,
                    'published_at' => now(),
                ],
            );

            $this->replaceSections($page, [
                ['section_type' => 'hero', 'title_en' => $businessUnit->name_en, 'body_en' => $message],
                ['section_type' => 'text', 'title_en' => 'Foundation', 'body_en' => 'This page is CMS-managed and ready for richer content in future phases.'],
            ]);
        }

        $menu = CmsMenu::query()->updateOrCreate(
            ['business_unit_id' => null, 'location' => 'main'],
            ['name' => 'Main Navigation', 'is_active' => true],
        );

        CmsMenuItem::query()->where('cms_menu_id', $menu->id)->delete();
        foreach ([
            ['label_en' => 'Home', 'label_ar' => 'الرئيسية', 'url' => '/'],
            ['label_en' => 'About', 'label_ar' => 'عن الشركة', 'url' => '/about'],
            ['label_en' => 'Business Units', 'label_ar' => 'وحدات الأعمال', 'url' => '/business-units'],
            ['label_en' => 'Contact', 'label_ar' => 'تواصل معنا', 'url' => '/contact'],
        ] as $index => $item) {
            CmsMenuItem::query()->create([...$item, 'cms_menu_id' => $menu->id, 'sort_order' => $index]);
        }
    }

    private function seedCompanyPage(string $slug, string $type, string $titleAr, string $titleEn, string $excerpt, array $sections): void
    {
        $page = CmsPage::query()->updateOrCreate(
            ['business_unit_id' => null, 'slug' => $slug],
            [
                'title_ar' => $titleAr,
                'title_en' => $titleEn,
                'page_type' => $type,
                'status' => 'published',
                'excerpt_en' => $excerpt,
                'seo_title_en' => $titleEn,
                'seo_description_en' => $excerpt,
                'published_at' => now(),
            ],
        );

        $this->replaceSections($page, $sections);
    }

    private function replaceSections(CmsPage $page, array $sections): void
    {
        $page->sections()->delete();
        foreach ($sections as $index => $section) {
            CmsSection::query()->create([
                ...$section,
                'cms_page_id' => $page->id,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}

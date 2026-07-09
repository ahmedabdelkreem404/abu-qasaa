<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\CMS\Infrastructure\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CmsPhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_fetch_published_home_page(): void
    {
        $this->seed();

        $this->getJson('/api/v1/public/cms/pages/home')
            ->assertOk()
            ->assertJsonPath('data.slug', 'home')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonCount(4, 'data.sections');
    }

    public function test_public_cannot_fetch_draft_or_archived_page(): void
    {
        $this->seed();
        CmsPage::query()->create([
            'title_ar' => 'Draft',
            'slug' => 'draft-page',
            'page_type' => 'standard',
            'status' => 'draft',
        ]);

        $this->getJson('/api/v1/public/cms/pages/draft-page')->assertNotFound();
    }

    public function test_public_can_fetch_business_unit_page_by_slug(): void
    {
        $this->seed();

        $this->getJson('/api/v1/public/cms/business-units/oils/page')
            ->assertOk()
            ->assertJsonPath('data.slug', 'oils')
            ->assertJsonPath('data.page_type', 'business_unit_landing');
    }

    public function test_public_can_submit_contact_inquiry(): void
    {
        $this->seed();

        $this->postJson('/api/v1/public/contact-inquiries', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'message' => 'I need more information.',
            'source_page' => '/contact',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'new');

        $this->assertDatabaseHas('contact_inquiries', ['email' => 'ahmed@example.com']);
    }

    public function test_unauthenticated_user_cannot_access_cms_dashboard_endpoints(): void
    {
        $this->seed();

        $this->getJson('/api/v1/cms/pages')
            ->assertUnauthorized();
    }

    public function test_user_with_cms_view_can_list_allowed_cms_pages(): void
    {
        $this->seed();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());

        $this->getJson('/api/v1/cms/pages')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'oils');
    }

    public function test_user_with_cms_manage_can_create_and_update_assigned_business_unit_page(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail();
        $oils = BusinessUnit::query()->where('slug', 'oils')->firstOrFail();
        Sanctum::actingAs($user);

        $pageId = $this->postJson('/api/v1/cms/pages', [
            'business_unit_id' => $oils->id,
            'title_ar' => 'صفحة اختبار',
            'title_en' => 'Test Page',
            'slug' => 'test-page',
            'page_type' => 'standard',
            'status' => 'draft',
        ])
            ->assertCreated()
            ->json('data.id');

        $this->patchJson("/api/v1/cms/pages/{$pageId}", [
            'title_en' => 'Updated Test Page',
        ])
            ->assertOk()
            ->assertJsonPath('data.title_en', 'Updated Test Page');
    }

    public function test_non_super_admin_cannot_manage_unassigned_business_unit_cms_page(): void
    {
        $this->seed();
        $datesPage = CmsPage::query()->where('slug', 'dates')->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());

        $this->patchJson("/api/v1/cms/pages/{$datesPage->id}", [
            'title_en' => 'Blocked',
        ])->assertForbidden();
    }

    public function test_super_admin_can_manage_company_level_cms_pages(): void
    {
        $this->seed();
        $home = CmsPage::query()->where('slug', 'home')->whereNull('business_unit_id')->firstOrFail();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $this->postJson("/api/v1/cms/pages/{$home->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'published');
    }

    public function test_contact_inquiry_status_can_be_updated_by_authorized_user(): void
    {
        $this->seed();
        $inquiry = ContactInquiry::query()->create([
            'name' => 'Lead',
            'message' => 'Please call.',
            'status' => 'new',
        ]);
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $this->putJson("/api/v1/cms/contact-inquiries/{$inquiry->id}/status", [
            'status' => 'in_progress',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');
    }
}

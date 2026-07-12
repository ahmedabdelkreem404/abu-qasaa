<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\ServicesRfq\Infrastructure\Models\RfqQuotation;
use App\Modules\ServicesRfq\Infrastructure\Models\RfqRequest;
use App\Modules\ServicesRfq\Infrastructure\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServicesRfqPhaseTwelveTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_published_services_only(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'import-export')->firstOrFail();
        Service::query()->create(['business_unit_id' => $unit->id, 'name' => 'Sea Freight', 'name_ar' => 'Sea Freight', 'slug' => 'sea-freight', 'status' => 'published']);
        Service::query()->create(['business_unit_id' => $unit->id, 'name' => 'Draft Service', 'name_ar' => 'Draft Service', 'slug' => 'draft-service', 'status' => 'draft']);

        $this->getJson('/api/v1/public/import-export/services')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'sea-freight'])
            ->assertJsonMissing(['slug' => 'draft-service']);
    }

    public function test_public_rfq_submission_creates_items_and_reference(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'import-export')->firstOrFail();
        $service = Service::query()->create(['business_unit_id' => $unit->id, 'name' => 'Air Freight', 'name_ar' => 'Air Freight', 'slug' => 'air-freight', 'status' => 'published']);

        $rfqNumber = $this->postJson('/api/v1/public/import-export/rfq-requests', [
            'service_id' => $service->id,
            'company_name' => 'Acme Import',
            'contact_name' => 'Omar Ali',
            'phone' => '01055556666',
            'email' => 'omar@example.com',
            'origin_country' => 'Egypt',
            'destination_country' => 'UAE',
            'items' => [
                ['item_name' => 'Dates boxes', 'quantity' => 100, 'unit' => 'carton'],
                ['item_name' => 'Packaging', 'quantity' => 50, 'unit' => 'box'],
            ],
        ])->assertCreated()->assertJsonPath('data.status', 'new')->json('data.rfq_number');

        $this->assertDatabaseHas('rfq_requests', ['rfq_number' => $rfqNumber, 'phone' => '01055556666']);
        $this->assertDatabaseCount('rfq_items', 2);
    }

    public function test_public_rfq_status_lookup_requires_contact_verification_and_hides_private_notes(): void
    {
        $this->seed();
        $rfq = $this->createRfq();
        $rfq->update(['notes' => 'private admin note']);

        $this->getJson("/api/v1/public/import-export/rfq-requests/{$rfq->rfq_number}/status?contact=wrong")->assertNotFound();
        $this->getJson("/api/v1/public/import-export/rfq-requests/{$rfq->rfq_number}/status?contact=01055556666")
            ->assertOk()
            ->assertJsonPath('data.rfq_number', $rfq->rfq_number)
            ->assertJsonMissingPath('data.notes');
    }

    public function test_dashboard_rfq_listing_is_business_unit_scoped(): void
    {
        $this->seed();
        $this->createRfq();

        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/services-rfq/rfq-requests')->assertOk()->assertJsonPath('meta.total', 0);

        Sanctum::actingAs(User::query()->where('email', 'importexport.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/services-rfq/rfq-requests')->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_quotation_totals_and_status_history_are_recorded(): void
    {
        $this->seed();
        $rfq = $this->createRfq();
        Sanctum::actingAs(User::query()->where('email', 'importexport.admin@abuqasaa.test')->firstOrFail());

        $quotationId = $this->postJson("/api/v1/services-rfq/rfq-requests/{$rfq->id}/quotations", [
            'currency' => 'EGP',
            'tax_total' => 140,
            'shipping_total' => 250,
            'items' => [
                ['description' => 'Sea freight', 'quantity' => 2, 'unit' => 'container', 'unit_price' => 1000],
                ['description' => 'Docs handling', 'quantity' => 1, 'unit' => 'service', 'unit_price' => 400],
            ],
        ])->assertCreated()->assertJsonPath('data.grand_total', '2790.00')->json('data.id');

        $this->postJson("/api/v1/services-rfq/quotations/{$quotationId}/send")->assertOk()->assertJsonPath('data.status', 'sent');
        $this->assertDatabaseHas('rfq_activity_logs', ['rfq_request_id' => $rfq->id, 'event' => 'quotation_sent']);
    }

    private function createRfq(): RfqRequest
    {
        $unit = BusinessUnit::query()->where('slug', 'import-export')->firstOrFail();
        $service = Service::query()->create(['business_unit_id' => $unit->id, 'name' => 'Sea Freight', 'name_ar' => 'Sea Freight', 'slug' => 'sea-freight', 'status' => 'published']);
        $rfq = RfqRequest::query()->create([
            'business_unit_id' => $unit->id,
            'service_id' => $service->id,
            'number' => 'RFQ-TEST',
            'rfq_number' => 'RFQ-TEST',
            'contact_name' => 'Omar Ali',
            'phone' => '01055556666',
            'email' => 'omar@example.com',
            'status' => 'new',
            'submitted_at' => now(),
        ]);
        $rfq->items()->create(['item_name' => 'Dates boxes', 'quantity' => 100, 'unit' => 'carton']);

        return $rfq;
    }
}

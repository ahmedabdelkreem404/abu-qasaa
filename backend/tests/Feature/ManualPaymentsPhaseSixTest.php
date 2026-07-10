<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitSetting;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Payments\Infrastructure\Models\ManualPaymentProof;
use App\Modules\Payments\Infrastructure\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ManualPaymentsPhaseSixTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_active_payment_methods_for_product_business_unit(): void
    {
        $this->seed();

        $this->getJson('/api/v1/public/dates/payment-methods')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'vodafone_cash')
            ->assertJsonMissing(['type' => 'paymob_placeholder']);
    }

    public function test_inactive_payment_method_is_not_shown_publicly(): void
    {
        $this->seed();
        PaymentMethod::query()->where('key', 'vodafone_cash')->whereHas('businessUnit', fn ($query) => $query->where('slug', 'dates'))->update(['is_active' => false]);

        $this->getJson('/api/v1/public/dates/payment-methods')
            ->assertOk()
            ->assertJsonMissing(['type' => 'vodafone_cash']);
    }

    public function test_public_can_submit_vodafone_cash_proof_for_matching_order_number_and_phone(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();

        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/manual-payment-proofs", $this->proofPayload($order))
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending_review')
            ->assertJsonMissingPath('data.admin_notes');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
        $this->assertDatabaseHas('payment_transactions', ['type' => 'manual_proof_submitted', 'status' => 'pending']);
    }

    public function test_public_cannot_submit_proof_with_wrong_phone(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();

        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/manual-payment-proofs", [...$this->proofPayload($order), 'phone' => 'wrong'])
            ->assertNotFound();
    }

    public function test_public_cannot_submit_proof_for_cancelled_order(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();
        $order->update(['status' => 'cancelled']);

        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/manual-payment-proofs", $this->proofPayload($order))
            ->assertForbidden();
    }

    public function test_public_cannot_submit_proof_when_manual_payment_setting_is_false(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();
        BusinessUnitSetting::query()->where('business_unit_id', $order->business_unit_id)->where('key', 'manual_payment_enabled')->update(['value' => false]);

        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/manual-payment-proofs", $this->proofPayload($order))
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_dashboard_payment_endpoints(): void
    {
        $this->seed();

        $this->getJson('/api/v1/payments/manual-proofs')->assertUnauthorized();
    }

    public function test_super_admin_can_list_all_manual_proofs(): void
    {
        $this->seed();
        $proof = $this->submitProof();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());

        $this->getJson('/api/v1/payments/manual-proofs')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $proof->id);
    }

    public function test_business_unit_admin_can_list_assigned_proofs_and_cannot_access_unassigned_proof(): void
    {
        $this->seed();
        $proof = $this->submitProof();

        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/payments/manual-proofs')->assertOk()->assertJsonPath('meta.total', 1);

        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/payments/manual-proofs')->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson("/api/v1/payments/manual-proofs/{$proof->id}")->assertForbidden();
    }

    public function test_authorized_user_can_approve_manual_proof_and_order_becomes_paid_and_confirmed(): void
    {
        $this->seed();
        $proof = $this->submitProof();
        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());

        $this->postJson("/api/v1/payments/manual-proofs/{$proof->id}/approve", ['admin_notes' => 'Looks good.'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('payments', ['id' => $proof->payment_id, 'status' => 'paid']);
        $this->assertDatabaseHas('orders', ['id' => $proof->order_id, 'payment_status' => 'paid', 'status' => 'confirmed']);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $proof->order_id, 'to_status' => 'confirmed']);
    }

    public function test_authorized_user_can_reject_manual_proof_without_deleting_it(): void
    {
        $this->seed();
        $proof = $this->submitProof();
        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());

        $this->postJson("/api/v1/payments/manual-proofs/{$proof->id}/reject", ['rejected_reason' => 'Reference not found.'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('manual_payment_proofs', ['id' => $proof->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('payments', ['id' => $proof->payment_id, 'status' => 'failed']);
    }

    public function test_cod_selection_creates_pending_payment_but_does_not_mark_order_paid(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();

        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/cash-on-delivery", ['phone' => '01000000000'])
            ->assertCreated()
            ->assertJsonPath('data.method_type', 'cash_on_delivery')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
        $this->assertDatabaseMissing('orders', ['id' => $order->id, 'payment_status' => 'paid']);
    }

    private function submitProof(): ManualPaymentProof
    {
        $order = $this->createPublicOrder();
        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/manual-payment-proofs", $this->proofPayload($order))->assertCreated();

        return ManualPaymentProof::query()->firstOrFail();
    }

    private function createPublicOrder(): Order
    {
        $product = Product::query()->where('slug', 'premium-medjool-dates-1kg')->firstOrFail();
        $token = $this->postJson('/api/v1/public/dates/cart')->json('data.session_token');
        $this->postJson("/api/v1/public/dates/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 1])->assertCreated();
        $orderNumber = $this->postJson('/api/v1/public/dates/checkout', [
            'session_token' => $token,
            'customer' => ['name' => 'Customer One', 'phone' => '01000000000', 'email' => 'customer@example.com'],
            'shipping_address' => ['recipient_name' => 'Customer One', 'phone' => '01000000000', 'governorate' => 'Cairo', 'city' => 'Cairo', 'street_address' => 'Test street'],
        ])->assertCreated()->json('data.order_number');

        return Order::query()->where('order_number', $orderNumber)->firstOrFail();
    }

    private function proofPayload(Order $order): array
    {
        return [
            'phone' => '01000000000',
            'method_key' => 'vodafone_cash',
            'amount' => $order->grand_total,
            'payer_name' => 'Customer One',
            'sender_account' => '01011111111',
            'transaction_reference' => 'VC-123',
            'proof_image' => 'manual-proofs/example.jpg',
            'notes' => 'Paid via Vodafone Cash.',
        ];
    }
}

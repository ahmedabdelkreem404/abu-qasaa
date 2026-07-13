<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\BusinessUnits\Infrastructure\Models\ActivityModule;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitModule;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnitSetting;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Payments\Infrastructure\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymobPhaseSevenTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_initiate_paymob_payment_for_matching_order_number_and_phone(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();

        $response = $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/paymob/initiate", ['phone' => '01000000000', 'method_key' => 'paymob_card'])
            ->assertCreated()
            ->assertJsonPath('data.payment_status', 'pending')
            ->assertJsonMissingPath('data.raw_payload_json');

        $this->assertStringStartsWith('fake-ref-', $response->json('data.provider_reference'));
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'provider' => 'paymob', 'status' => 'pending']);
        $this->assertDatabaseHas('payment_transactions', ['type' => 'paymob_initiated', 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'pending']);
    }

    public function test_public_cannot_initiate_paymob_with_wrong_phone_or_disabled_rules(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();

        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/paymob/initiate", ['phone' => 'wrong', 'method_key' => 'paymob_card'])->assertNotFound();

        BusinessUnitSetting::query()->where('business_unit_id', $order->business_unit_id)->where('key', 'paymob_enabled')->update(['value' => false]);
        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/paymob/initiate", ['phone' => '01000000000', 'method_key' => 'paymob_card'])->assertForbidden();

        BusinessUnitSetting::query()->where('business_unit_id', $order->business_unit_id)->where('key', 'paymob_enabled')->update(['value' => true]);
        $module = ActivityModule::query()->where('key', 'paymob')->firstOrFail();
        BusinessUnitModule::query()->where('business_unit_id', $order->business_unit_id)->where('activity_module_id', $module->id)->update(['is_enabled' => false]);
        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/paymob/initiate", ['phone' => '01000000000', 'method_key' => 'paymob_card'])->assertNotFound();
    }

    public function test_public_cannot_initiate_paymob_for_already_paid_order(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();
        $order->update(['payment_status' => 'paid']);

        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/paymob/initiate", ['phone' => '01000000000', 'method_key' => 'paymob_card'])->assertStatus(422);
    }

    public function test_valid_fake_paymob_success_callback_marks_payment_and_order_paid_and_is_idempotent(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();
        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/paymob/initiate", ['phone' => '01000000000', 'method_key' => 'paymob_card'])->assertCreated();
        $payment = Payment::query()->firstOrFail();
        $payload = $this->signedPayload($payment, true);

        $this->postJson('/api/v1/payments/paymob/callback', $payload)->assertOk()->assertJsonPath('data.status', 'paid');
        $this->postJson('/api/v1/payments/paymob/callback', $payload)->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid', 'status' => 'confirmed']);
        $this->assertSame(1, $payment->fresh()->transactions()->where('type', 'paymob_paid')->count());
    }

    public function test_invalid_signature_callback_is_rejected_and_failed_callback_does_not_confirm_order(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();
        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/paymob/initiate", ['phone' => '01000000000', 'method_key' => 'paymob_card'])->assertCreated();
        $payment = Payment::query()->firstOrFail();

        $this->postJson('/api/v1/payments/paymob/callback', [...$this->signedPayload($payment, true), 'hmac' => 'bad'])->assertForbidden();
        $this->postJson('/api/v1/payments/paymob/callback', $this->signedPayload($payment, false))->assertOk()->assertJsonPath('data.status', 'failed');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'failed']);
        $this->assertDatabaseMissing('orders', ['id' => $order->id, 'status' => 'confirmed']);
    }

    public function test_public_payment_status_and_dashboard_paymob_transactions_are_scoped(): void
    {
        $this->seed();
        $order = $this->createPublicOrder();
        $this->postJson("/api/v1/public/dates/orders/{$order->order_number}/paymob/initiate", ['phone' => '01000000000', 'method_key' => 'paymob_card'])->assertCreated();

        $this->getJson("/api/v1/public/dates/orders/{$order->order_number}/payment-status?phone=01000000000")
            ->assertOk()
            ->assertJsonMissingPath('data.payment.raw_payload_json');

        $this->getJson('/api/v1/payments/paymob/transactions')->assertUnauthorized();
        Sanctum::actingAs(User::query()->where('email', 'admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/payments/paymob/transactions')->assertOk()->assertJsonPath('meta.total', 1);
        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/payments/paymob/transactions')->assertOk()->assertJsonPath('meta.total', 0);
    }

    private function signedPayload(Payment $payment, bool $success): array
    {
        $reference = $payment->provider_reference;

        return [
            'payment_id' => $payment->id,
            'id' => $reference,
            'provider_reference' => $reference,
            'status' => $success ? 'success' : 'failed',
            'success' => $success,
            'hmac' => hash_hmac('sha512', $reference, 'fake-paymob-secret'),
        ];
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
}

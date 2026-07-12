<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Audit\Application\Services\AuditLogger;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Infrastructure\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportsAuditPhaseThirteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_report_is_business_unit_scoped(): void
    {
        $this->seed();
        $this->createOrder('dates');

        Sanctum::actingAs(User::query()->where('email', 'oils.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/reports/executive-summary')->assertOk()->assertJsonPath('data.orders_count', 0);

        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());
        $this->getJson('/api/v1/reports/executive-summary')->assertOk()->assertJsonPath('data.orders_count', 1);
    }

    public function test_orders_csv_export_is_scoped_and_arabic_safe(): void
    {
        $this->seed();
        $order = $this->createOrder('dates');
        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());

        $this->get('/api/v1/reports/commerce/orders/export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertSee('order_number')
            ->assertSee($order->order_number);
    }

    public function test_audit_logger_redacts_sensitive_values(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();

        app(AuditLogger::class)->log($unit->id, null, 'updated', 'secret.changed', null, null, ['password' => 'secret', 'token' => 'abc', 'name' => 'Visible']);

        $this->assertDatabaseHas('audit_logs', ['business_unit_id' => $unit->id, 'event' => 'secret.changed']);
        $this->assertDatabaseMissing('audit_logs', ['new_values_json' => json_encode(['password' => 'secret'])]);
        $this->getJson('/api/v1/audit-logs')->assertUnauthorized();
    }

    public function test_authorized_user_can_view_scoped_audit_logs(): void
    {
        $this->seed();
        $unit = BusinessUnit::query()->where('slug', 'dates')->firstOrFail();
        app(AuditLogger::class)->log($unit->id, null, 'created', 'order.created', Order::class, 1, ['status' => 'pending']);
        Sanctum::actingAs(User::query()->where('email', 'dates.admin@abuqasaa.test')->firstOrFail());

        $this->getJson('/api/v1/audit-logs')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.event', 'order.created');
    }

    private function createOrder(string $businessSlug): Order
    {
        $product = Product::query()->whereHas('businessUnit', fn ($query) => $query->where('slug', $businessSlug))->firstOrFail();
        $token = $this->postJson("/api/v1/public/{$businessSlug}/cart")->json('data.session_token');
        $this->postJson("/api/v1/public/{$businessSlug}/cart/{$token}/items", ['product_id' => $product->id, 'quantity' => 1])->assertCreated();
        $orderNumber = $this->postJson("/api/v1/public/{$businessSlug}/checkout", [
            'session_token' => $token,
            'customer' => ['name' => 'عميل اختبار', 'phone' => '01000000000', 'email' => 'customer@example.com'],
            'shipping_address' => ['recipient_name' => 'عميل اختبار', 'phone' => '01000000000', 'city' => 'Cairo', 'street_address' => 'Test street'],
        ])->assertCreated()->json('data.order_number');

        return Order::query()->where('order_number', $orderNumber)->firstOrFail();
    }
}

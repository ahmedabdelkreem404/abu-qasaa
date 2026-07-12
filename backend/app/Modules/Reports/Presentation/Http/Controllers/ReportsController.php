<?php

namespace App\Modules\Reports\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Inventory\Infrastructure\Models\StockItem;
use App\Modules\RealEstate\Infrastructure\Models\RealEstateLead;
use App\Modules\ServicesRfq\Infrastructure\Models\RfqRequest;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function executive(Request $request)
    {
        $ids = $this->businessUnitIds($request);

        return ApiResponse::success([
            'orders_count' => Order::query()->whereIn('business_unit_id', $ids)->count(),
            'orders_value' => (float) Order::query()->whereIn('business_unit_id', $ids)->sum('grand_total'),
            'customers_count' => Customer::query()->whereIn('business_unit_id', $ids)->count(),
            'low_stock_count' => StockItem::query()->whereIn('business_unit_id', $ids)->whereColumn('quantity_on_hand', '<=', 'reorder_level')->count(),
            'real_estate_leads_count' => class_exists(RealEstateLead::class) ? RealEstateLead::query()->whereIn('business_unit_id', $ids)->count() : 0,
            'rfq_count' => class_exists(RfqRequest::class) ? RfqRequest::query()->whereIn('business_unit_id', $ids)->count() : 0,
        ], 'Executive summary retrieved successfully');
    }

    public function exportOrders(Request $request)
    {
        $ids = $this->businessUnitIds($request);
        $orders = Order::query()->whereIn('business_unit_id', $ids)->orderByDesc('id')->get();
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['order_number', 'customer_name', 'customer_phone', 'status', 'payment_status', 'grand_total', 'currency']);
        foreach ($orders as $order) {
            fputcsv($out, [$order->order_number, $order->customer_name, $order->customer_phone, $order->status, $order->payment_status, $order->grand_total, $order->currency]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="orders-export.csv"',
        ]);
    }

    private function businessUnitIds(Request $request)
    {
        if ($request->user()->isSuperAdmin()) {
            return \App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit::query()->pluck('id');
        }

        return $request->user()->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id');
    }
}

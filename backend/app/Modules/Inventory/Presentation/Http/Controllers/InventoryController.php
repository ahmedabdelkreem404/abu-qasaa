<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Application\Services\AccessControlService;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Domain\Enums\StockTransferStatus;
use App\Modules\Inventory\Infrastructure\Models\Branch;
use App\Modules\Inventory\Infrastructure\Models\StockItem;
use App\Modules\Inventory\Infrastructure\Models\StockMovement;
use App\Modules\Inventory\Infrastructure\Models\StockTransfer;
use App\Modules\Inventory\Infrastructure\Models\Warehouse;
use App\Modules\Inventory\Presentation\Http\Requests\BranchRequest;
use App\Modules\Inventory\Presentation\Http\Requests\StockAdjustmentRequest;
use App\Modules\Inventory\Presentation\Http\Requests\StockReceiveRequest;
use App\Modules\Inventory\Presentation\Http\Requests\StockTransferRequest;
use App\Modules\Inventory\Presentation\Http\Requests\WarehouseRequest;
use App\Modules\Inventory\Presentation\Http\Resources\BranchResource;
use App\Modules\Inventory\Presentation\Http\Resources\InventorySummaryResource;
use App\Modules\Inventory\Presentation\Http\Resources\PublicAvailabilityResource;
use App\Modules\Inventory\Presentation\Http\Resources\StockItemResource;
use App\Modules\Inventory\Presentation\Http\Resources\StockMovementResource;
use App\Modules\Inventory\Presentation\Http\Resources\StockReservationResource;
use App\Modules\Inventory\Presentation\Http\Resources\StockTransferResource;
use App\Modules\Inventory\Presentation\Http\Resources\WarehouseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(
        private readonly AccessControlService $accessControl,
        private readonly InventoryService $inventory,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $businessUnitId = $request->integer('business_unit_id') ?: null;
        if ($error = $this->validateOptionalScope($request, $businessUnitId)) {
            return $error;
        }

        $scope = fn ($query) => $this->scopeQuery($request, $query, $businessUnitId);
        $lowStock = (clone $scope(StockItem::query()))->get()->filter(fn (StockItem $item) => $item->quantity_available <= (float) $item->reorder_level)->count();

        return ApiResponse::success(new InventorySummaryResource([
            'branches_count' => (clone $scope(Branch::query()))->count(),
            'warehouses_count' => (clone $scope(Warehouse::query()))->count(),
            'stock_items_count' => (clone $scope(StockItem::query()))->count(),
            'low_stock_count' => $lowStock,
            'reserved_quantity' => (string) (clone $scope(StockItem::query()))->sum('quantity_reserved'),
            'open_transfers_count' => (clone $scope(StockTransfer::query()))->whereIn('status', [StockTransferStatus::Draft->value, StockTransferStatus::Approved->value])->count(),
        ]), 'Inventory summary retrieved successfully');
    }

    public function branches(Request $request): JsonResponse
    {
        $query = $this->scopeQuery($request, Branch::query()->with('businessUnit'), $request->integer('business_unit_id') ?: null);

        return ApiResponse::paginated($query->orderBy('sort_order')->orderBy('id')->paginate(20)->through(fn (Branch $branch) => BranchResource::make($branch)->resolve()), 'Branches retrieved successfully');
    }

    public function storeBranch(BranchRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($error = $this->validateScope($request, $data['business_unit_id'])) {
            return $error;
        }

        return ApiResponse::success(BranchResource::make(Branch::query()->create($data)->load('businessUnit')), 'Branch created successfully', 201);
    }

    public function showBranch(Request $request, Branch $branch): JsonResponse
    {
        if ($error = $this->validateScope($request, $branch->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(BranchResource::make($branch->load('businessUnit')), 'Branch retrieved successfully');
    }

    public function updateBranch(BranchRequest $request, Branch $branch): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateScope($request, $branch->business_unit_id)) || ($error = $this->validateScope($request, $data['business_unit_id']))) {
            return $error;
        }
        $branch->update($data);

        return ApiResponse::success(BranchResource::make($branch->refresh()->load('businessUnit')), 'Branch updated successfully');
    }

    public function destroyBranch(Request $request, Branch $branch): JsonResponse
    {
        if ($error = $this->validateScope($request, $branch->business_unit_id)) {
            return $error;
        }
        $branch->delete();

        return ApiResponse::success(BranchResource::make($branch), 'Branch archived successfully');
    }

    public function publicBranches(string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);

        return ApiResponse::success(BranchResource::collection(Branch::query()->where('business_unit_id', $businessUnit->id)->where('status', 'active')->where('is_public', true)->orderBy('sort_order')->get()), 'Public branches retrieved successfully');
    }

    public function warehouses(Request $request): JsonResponse
    {
        $query = $this->scopeQuery($request, Warehouse::query()->with(['branch', 'businessUnit']), $request->integer('business_unit_id') ?: null);

        return ApiResponse::paginated($query->orderBy('sort_order')->orderBy('id')->paginate(20)->through(fn (Warehouse $warehouse) => WarehouseResource::make($warehouse)->resolve()), 'Warehouses retrieved successfully');
    }

    public function storeWarehouse(WarehouseRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateScope($request, $data['business_unit_id'])) || ($error = $this->validateBranch($data))) {
            return $error;
        }
        $this->clearDefaultWarehouse($data);

        return ApiResponse::success(WarehouseResource::make(Warehouse::query()->create($data)->load('branch')), 'Warehouse created successfully', 201);
    }

    public function showWarehouse(Request $request, Warehouse $warehouse): JsonResponse
    {
        if ($error = $this->validateScope($request, $warehouse->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(WarehouseResource::make($warehouse->load('branch')), 'Warehouse retrieved successfully');
    }

    public function updateWarehouse(WarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateScope($request, $warehouse->business_unit_id)) || ($error = $this->validateScope($request, $data['business_unit_id'])) || ($error = $this->validateBranch($data))) {
            return $error;
        }
        $this->clearDefaultWarehouse($data, $warehouse->id);
        $warehouse->update($data);

        return ApiResponse::success(WarehouseResource::make($warehouse->refresh()->load('branch')), 'Warehouse updated successfully');
    }

    public function destroyWarehouse(Request $request, Warehouse $warehouse): JsonResponse
    {
        if ($error = $this->validateScope($request, $warehouse->business_unit_id)) {
            return $error;
        }
        $warehouse->delete();

        return ApiResponse::success(WarehouseResource::make($warehouse), 'Warehouse archived successfully');
    }

    public function stockItems(Request $request): JsonResponse
    {
        $query = $this->scopeQuery($request, StockItem::query()->with(['warehouse', 'product', 'variant']), $request->integer('business_unit_id') ?: null);
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->integer('warehouse_id'));
        }

        return ApiResponse::paginated($query->orderByDesc('last_movement_at')->orderByDesc('id')->paginate(20)->through(fn (StockItem $item) => StockItemResource::make($item)->resolve()), 'Stock items retrieved successfully');
    }

    public function showStockItem(Request $request, StockItem $stockItem): JsonResponse
    {
        if ($error = $this->validateScope($request, $stockItem->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(StockItemResource::make($stockItem->load(['warehouse', 'product', 'variant'])), 'Stock item retrieved successfully');
    }

    public function receive(StockReceiveRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($error = $this->validateStockPayload($request, $data)) {
            return $error;
        }

        return ApiResponse::success(StockItemResource::make($this->inventory->receive($data, $request->user())), 'Stock received successfully', 201);
    }

    public function adjust(StockAdjustmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($error = $this->validateStockPayload($request, $data)) {
            return $error;
        }

        return ApiResponse::success(StockItemResource::make($this->inventory->adjust($data, $request->user())), 'Stock adjusted successfully');
    }

    public function movements(Request $request): JsonResponse
    {
        $query = $this->scopeQuery($request, StockMovement::query()->with(['warehouse', 'product', 'variant', 'createdBy']), $request->integer('business_unit_id') ?: null);
        if ($request->filled('stock_item_id')) {
            $query->where('stock_item_id', $request->integer('stock_item_id'));
        }

        return ApiResponse::paginated($query->latest()->paginate(20)->through(fn (StockMovement $movement) => StockMovementResource::make($movement)->resolve()), 'Stock movements retrieved successfully');
    }

    public function transfers(Request $request): JsonResponse
    {
        $query = $this->scopeQuery($request, StockTransfer::query()->with(['fromWarehouse', 'toWarehouse', 'items.product', 'items.variant']), $request->integer('business_unit_id') ?: null);

        return ApiResponse::paginated($query->latest()->paginate(20)->through(fn (StockTransfer $transfer) => StockTransferResource::make($transfer)->resolve()), 'Transfers retrieved successfully');
    }

    public function storeTransfer(StockTransferRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateScope($request, $data['business_unit_id'])) || ($error = $this->validateTransferPayload($data))) {
            return $error;
        }

        $transfer = DB::transaction(function () use ($data, $request): StockTransfer {
            $transfer = StockTransfer::query()->create([
                'business_unit_id' => $data['business_unit_id'],
                'transfer_number' => 'TRF-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'status' => StockTransferStatus::Draft->value,
                'requested_by' => $request->user()->id,
                'requested_at' => now(),
                'note' => $data['note'] ?? null,
            ]);
            $transfer->items()->createMany($data['items']);

            return $transfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'items.variant']);
        });

        return ApiResponse::success(StockTransferResource::make($transfer), 'Transfer created successfully', 201);
    }

    public function showTransfer(Request $request, StockTransfer $transfer): JsonResponse
    {
        if ($error = $this->validateScope($request, $transfer->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(StockTransferResource::make($transfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'items.variant'])), 'Transfer retrieved successfully');
    }

    public function approveTransfer(Request $request, StockTransfer $transfer): JsonResponse
    {
        if ($error = $this->validateScope($request, $transfer->business_unit_id)) {
            return $error;
        }
        $transfer->update(['status' => StockTransferStatus::Approved->value, 'approved_by' => $request->user()->id, 'approved_at' => now()]);

        return ApiResponse::success(StockTransferResource::make($transfer->refresh()->load(['fromWarehouse', 'toWarehouse', 'items.product', 'items.variant'])), 'Transfer approved successfully');
    }

    public function completeTransfer(Request $request, StockTransfer $transfer): JsonResponse
    {
        if ($error = $this->validateScope($request, $transfer->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(StockTransferResource::make($this->inventory->completeTransfer($transfer, $request->user())), 'Transfer completed successfully');
    }

    public function cancelTransfer(Request $request, StockTransfer $transfer): JsonResponse
    {
        if ($error = $this->validateScope($request, $transfer->business_unit_id)) {
            return $error;
        }
        $transfer->update(['status' => StockTransferStatus::Cancelled->value, 'cancelled_at' => now()]);

        return ApiResponse::success(StockTransferResource::make($transfer->refresh()->load(['fromWarehouse', 'toWarehouse', 'items.product', 'items.variant'])), 'Transfer cancelled successfully');
    }

    public function fulfillOrder(Request $request, Order $order): JsonResponse
    {
        if ($error = $this->validateScope($request, $order->business_unit_id)) {
            return $error;
        }
        $this->inventory->fulfillOrder($order);

        return ApiResponse::success(StockReservationResource::collection($order->stockReservations()->with('warehouse')->get()), 'Order stock fulfilled successfully');
    }

    public function publicAvailability(string $businessSlug, string $productSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $product = Product::query()->where('business_unit_id', $businessUnit->id)->where('slug', $productSlug)->where('status', 'published')->where('visibility', 'public')->firstOrFail();

        return ApiResponse::success(new PublicAvailabilityResource($this->inventory->availability($businessUnit, $product)), 'Product availability retrieved successfully');
    }

    private function validateOptionalScope(Request $request, ?int $businessUnitId): ?JsonResponse
    {
        return $businessUnitId ? $this->validateScope($request, $businessUnitId) : null;
    }

    private function validateScope(Request $request, int|string $businessUnitId): ?JsonResponse
    {
        $businessUnit = BusinessUnit::query()->findOrFail($businessUnitId);
        if (! $this->accessControl->canUseModule($request->user(), $businessUnit, 'inventory')) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return null;
    }

    private function scopeQuery(Request $request, $query, ?int $businessUnitId)
    {
        if ($businessUnitId) {
            return $query->where('business_unit_id', $businessUnitId);
        }
        if ($request->user()->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn('business_unit_id', $request->user()->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id'));
    }

    private function validateBranch(array $data): ?JsonResponse
    {
        if (empty($data['branch_id'])) {
            return null;
        }

        return Branch::query()->whereKey($data['branch_id'])->where('business_unit_id', $data['business_unit_id'])->exists()
            ? null
            : ApiResponse::error('Branch must belong to the same business unit.', 422);
    }

    private function validateStockPayload(Request $request, array $data): ?JsonResponse
    {
        if ($error = $this->validateScope($request, $data['business_unit_id'])) {
            return $error;
        }
        if (! Warehouse::query()->whereKey($data['warehouse_id'])->where('business_unit_id', $data['business_unit_id'])->exists()) {
            return ApiResponse::error('Warehouse must belong to the same business unit.', 422);
        }
        if (! Product::query()->whereKey($data['product_id'])->where('business_unit_id', $data['business_unit_id'])->exists()) {
            return ApiResponse::error('Product must belong to the same business unit.', 422);
        }
        if (! empty($data['product_variant_id']) && ! ProductVariant::query()->whereKey($data['product_variant_id'])->where('product_id', $data['product_id'])->exists()) {
            return ApiResponse::error('Product variant must belong to the product.', 422);
        }

        return null;
    }

    private function validateTransferPayload(array $data): ?JsonResponse
    {
        foreach (['from_warehouse_id', 'to_warehouse_id'] as $warehouseKey) {
            if (! Warehouse::query()->whereKey($data[$warehouseKey])->where('business_unit_id', $data['business_unit_id'])->exists()) {
                return ApiResponse::error('Transfer warehouses must belong to the same business unit.', 422);
            }
        }
        foreach ($data['items'] as $item) {
            if (! Product::query()->whereKey($item['product_id'])->where('business_unit_id', $data['business_unit_id'])->exists()) {
                return ApiResponse::error('Transfer products must belong to the same business unit.', 422);
            }
            if (! empty($item['product_variant_id']) && ! ProductVariant::query()->whereKey($item['product_variant_id'])->where('product_id', $item['product_id'])->exists()) {
                return ApiResponse::error('Transfer variants must belong to their product.', 422);
            }
        }

        return null;
    }

    private function clearDefaultWarehouse(array $data, ?int $exceptId = null): void
    {
        if (empty($data['is_default'])) {
            return;
        }
        Warehouse::query()->where('business_unit_id', $data['business_unit_id'])->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))->update(['is_default' => false]);
    }

    private function publicBusinessUnit(string $slug): BusinessUnit
    {
        $businessUnit = BusinessUnit::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        abort_unless($businessUnit->moduleAssignments()->whereHas('activityModule', fn ($query) => $query->where('key', 'inventory'))->where('is_enabled', true)->exists(), 404);

        return $businessUnit;
    }
}

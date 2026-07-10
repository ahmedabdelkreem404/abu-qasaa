<?php

namespace App\Modules\Commerce\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Commerce\Application\Actions\ApproveWholesaleApplicationAction;
use App\Modules\Commerce\Application\Actions\AssignCustomerPriceListAction;
use App\Modules\Commerce\Application\Actions\GetWholesaleApplicationAction;
use App\Modules\Commerce\Application\Actions\ListWholesaleApplicationsAction;
use App\Modules\Commerce\Application\Actions\ListWholesaleCustomersAction;
use App\Modules\Commerce\Application\Actions\RejectWholesaleApplicationAction;
use App\Modules\Commerce\Application\Actions\SubmitWholesaleApplicationAction;
use App\Modules\Commerce\Application\Actions\UpdateWholesaleCustomerAction;
use App\Modules\Commerce\Application\Services\WholesaleAccessService;
use App\Modules\Commerce\Application\Services\WholesalePricingService;
use App\Modules\Commerce\Domain\Enums\WholesaleCustomerStatus;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Commerce\Infrastructure\Models\WholesaleApplication;
use App\Modules\Commerce\Presentation\Http\Resources\WholesaleAccessResource;
use App\Modules\Commerce\Presentation\Http\Resources\WholesaleApplicationResource;
use App\Modules\Commerce\Presentation\Http\Resources\WholesaleCustomerResource;
use App\Modules\Commerce\Presentation\Http\Resources\WholesalePricingResource;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Application\Services\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WholesaleController extends Controller
{
    public function __construct(
        private readonly AccessControlService $accessControl,
        private readonly WholesaleAccessService $access,
        private readonly WholesalePricingService $pricing,
    ) {}

    public function apply(Request $request, string $businessSlug, SubmitWholesaleApplicationAction $action): JsonResponse
    {
        $businessUnit = $this->publicWholesaleBusinessUnit($businessSlug);
        $data = $request->validate([
            'applicant_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'shop_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'commercial_record' => ['nullable', 'string', 'max:255'],
            'governorate' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2048'],
            'message' => ['nullable', 'string', 'max:4096'],
        ]);

        return ApiResponse::success(new WholesaleApplicationResource($action->handle($businessUnit, $data), true), 'Wholesale application submitted successfully', 201);
    }

    public function status(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicWholesaleBusinessUnit($businessSlug);
        $data = $request->validate(['phone' => ['required', 'string', 'max:255']]);
        $customer = Customer::query()->where('business_unit_id', $businessUnit->id)->where('phone', $data['phone'])->whereNotNull('wholesale_status')->first();
        if ($customer) {
            return ApiResponse::success(['type' => 'customer', 'status' => $customer->wholesale_status], 'Wholesale status retrieved successfully');
        }

        $application = WholesaleApplication::query()->where('business_unit_id', $businessUnit->id)->where('phone', $data['phone'])->latest()->first();

        return ApiResponse::success([
            'type' => $application ? 'application' : 'none',
            'status' => $application?->status,
        ], 'Wholesale status retrieved successfully');
    }

    public function access(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicWholesaleBusinessUnit($businessSlug);
        $data = $request->validate(['phone' => ['required', 'string', 'max:255']]);
        $customer = Customer::query()
            ->with(['businessUnit', 'priceList'])
            ->where('business_unit_id', $businessUnit->id)
            ->where('phone', $data['phone'])
            ->where('wholesale_status', WholesaleCustomerStatus::Approved->value)
            ->first();

        abort_unless($customer, 403);

        return ApiResponse::success(new WholesaleAccessResource([
            'token' => $this->access->issueAccessToken($customer),
            'customer' => $customer,
        ]), 'Wholesale access granted');
    }

    public function products(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicWholesaleBusinessUnit($businessSlug);
        $customer = $this->approvedCustomerOrAbort($request, $businessUnit);
        $products = Product::query()
            ->with(['category', 'brand', 'images', 'variants'])
            ->where('business_unit_id', $businessUnit->id)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->paginate((int) $request->query('per_page', 15));

        return ApiResponse::paginated($products->through(fn (Product $product) => $this->pricingPayload($product, $customer)), 'Wholesale products retrieved successfully');
    }

    public function product(Request $request, string $businessSlug, string $productSlug): JsonResponse
    {
        $businessUnit = $this->publicWholesaleBusinessUnit($businessSlug);
        $customer = $this->approvedCustomerOrAbort($request, $businessUnit);
        $product = Product::query()
            ->with(['category', 'brand', 'images', 'variants'])
            ->where('business_unit_id', $businessUnit->id)
            ->where('slug', $productSlug)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->firstOrFail();

        return ApiResponse::success(new WholesalePricingResource($this->pricingPayload($product, $customer)), 'Wholesale product retrieved successfully');
    }

    public function applications(Request $request, ListWholesaleApplicationsAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (WholesaleApplication $application) => WholesaleApplicationResource::make($application)->resolve()),
            'Wholesale applications retrieved successfully',
        );
    }

    public function showApplication(Request $request, WholesaleApplication $wholesaleApplication, GetWholesaleApplicationAction $action): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $wholesaleApplication->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(WholesaleApplicationResource::make($action->handle($wholesaleApplication)), 'Wholesale application retrieved successfully');
    }

    public function approveApplication(Request $request, WholesaleApplication $wholesaleApplication, ApproveWholesaleApplicationAction $action): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $wholesaleApplication->business_unit_id)) {
            return $error;
        }
        $data = $request->validate(['price_list_id' => ['nullable', 'exists:price_lists,id'], 'notes' => ['nullable', 'string']]);

        return ApiResponse::success(WholesaleApplicationResource::make($action->handle($wholesaleApplication, $data, $request->user())), 'Wholesale application approved successfully');
    }

    public function rejectApplication(Request $request, WholesaleApplication $wholesaleApplication, RejectWholesaleApplicationAction $action): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $wholesaleApplication->business_unit_id)) {
            return $error;
        }
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:4096'], 'notes' => ['nullable', 'string']]);

        return ApiResponse::success(WholesaleApplicationResource::make($action->handle($wholesaleApplication, $data, $request->user())), 'Wholesale application rejected successfully');
    }

    public function customers(Request $request, ListWholesaleCustomersAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (Customer $customer) => WholesaleCustomerResource::make($customer)->resolve()),
            'Wholesale customers retrieved successfully',
        );
    }

    public function showCustomer(Request $request, Customer $customer): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $customer->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(WholesaleCustomerResource::make($customer->load(['businessUnit', 'priceList'])), 'Wholesale customer retrieved successfully');
    }

    public function updateCustomer(Request $request, Customer $customer, UpdateWholesaleCustomerAction $action): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $customer->business_unit_id)) {
            return $error;
        }
        $data = $request->validate([
            'type' => ['sometimes', 'string', 'in:individual,shop,company,distributor'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'commercial_record' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        return ApiResponse::success(WholesaleCustomerResource::make($action->handle($customer, $data)), 'Wholesale customer updated successfully');
    }

    public function assignPriceList(Request $request, Customer $customer, AssignCustomerPriceListAction $action): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $customer->business_unit_id)) {
            return $error;
        }
        $data = $request->validate(['price_list_id' => ['required', 'exists:price_lists,id'], 'notes' => ['nullable', 'string']]);

        return ApiResponse::success(WholesaleCustomerResource::make($action->handle($customer, $data)), 'Wholesale price list assigned successfully');
    }

    public function approveCustomer(Request $request, Customer $customer): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $customer->business_unit_id)) {
            return $error;
        }
        $customer->update([
            'approval_status' => 'approved',
            'wholesale_status' => WholesaleCustomerStatus::Approved->value,
            'approved_at' => now(),
            'approved_by' => $request->user()?->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ]);

        return ApiResponse::success(WholesaleCustomerResource::make($customer->load(['businessUnit', 'priceList'])), 'Wholesale customer approved successfully');
    }

    public function rejectCustomer(Request $request, Customer $customer): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $customer->business_unit_id)) {
            return $error;
        }
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:4096']]);
        $customer->update([
            'approval_status' => 'rejected',
            'wholesale_status' => WholesaleCustomerStatus::Rejected->value,
            'rejected_at' => now(),
            'rejected_by' => $request->user()?->id,
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return ApiResponse::success(WholesaleCustomerResource::make($customer->load(['businessUnit', 'priceList'])), 'Wholesale customer rejected successfully');
    }

    public function priceLists(Request $request): JsonResponse
    {
        $query = PriceList::query()->whereIn('type', ['wholesale', 'distributor', 'special'])->where('is_active', true);
        if ($request->user()?->isSuperAdmin() !== true) {
            $query->whereIn('business_unit_id', $request->user()->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id'));
        }

        return ApiResponse::success($query->get(), 'Wholesale price lists retrieved successfully');
    }

    public function pricingPreview(Request $request, Customer $customer): JsonResponse
    {
        if ($error = $this->validateWholesaleScope($request, $customer->business_unit_id)) {
            return $error;
        }
        $products = Product::query()->where('business_unit_id', $customer->business_unit_id)->where('status', 'published')->limit(20)->get();

        return ApiResponse::success($products->map(fn (Product $product) => $this->pricingPayload($product, $customer))->values(), 'Wholesale pricing preview retrieved successfully');
    }

    private function pricingPayload(Product $product, Customer $customer): array
    {
        $price = $this->pricing->resolveProductPrice($product, $customer, max(1, (int) $product->min_order_quantity, (int) request()->query('quantity', 999999)));

        return [
            'product_id' => $product->id,
            'product_slug' => $product->slug,
            'name_ar' => $product->name_ar,
            'name_en' => $product->name_en,
            'sku' => $product->sku,
            'currency' => $product->currency,
            'base_price' => $product->base_price,
            'wholesale_price' => number_format($price['unit_price'], 2, '.', ''),
            ...$price,
        ];
    }

    private function publicWholesaleBusinessUnit(string $slug): BusinessUnit
    {
        $businessUnit = BusinessUnit::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        abort_unless($this->access->wholesaleEnabled($businessUnit), 404);

        return $businessUnit;
    }

    private function approvedCustomerOrAbort(Request $request, BusinessUnit $businessUnit): Customer
    {
        $customer = $this->access->approvedCustomer(
            $businessUnit,
            $request->query('phone', $request->header('X-Wholesale-Phone')),
            $request->bearerToken() ?: $request->query('token', $request->header('X-Wholesale-Token')),
        );
        abort_unless($customer, 403);

        return $customer;
    }

    private function validateWholesaleScope(Request $request, int|string $businessUnitId): ?JsonResponse
    {
        $businessUnit = BusinessUnit::query()->findOrFail($businessUnitId);
        if (! $this->accessControl->canAccessBusinessUnit($request->user(), $businessUnit) || ! $this->access->wholesaleEnabled($businessUnit)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return null;
    }
}

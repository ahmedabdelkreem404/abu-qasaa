<?php

namespace App\Modules\Commerce\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Commerce\Application\Actions\AddCartItemAction;
use App\Modules\Commerce\Application\Actions\CancelOrderAction;
use App\Modules\Commerce\Application\Actions\ClearCartAction;
use App\Modules\Commerce\Application\Actions\CreateCustomerAction;
use App\Modules\Commerce\Application\Actions\CreateOrderFromCartAction;
use App\Modules\Commerce\Application\Actions\GetCartAction;
use App\Modules\Commerce\Application\Actions\GetCustomerAction;
use App\Modules\Commerce\Application\Actions\GetOrCreateCartAction;
use App\Modules\Commerce\Application\Actions\GetOrderAction;
use App\Modules\Commerce\Application\Actions\ListCustomersAction;
use App\Modules\Commerce\Application\Actions\ListOrdersAction;
use App\Modules\Commerce\Application\Actions\RemoveCartItemAction;
use App\Modules\Commerce\Application\Actions\UpdateCartItemAction;
use App\Modules\Commerce\Application\Actions\UpdateCustomerAction;
use App\Modules\Commerce\Application\Actions\UpdateOrderStatusAction;
use App\Modules\Commerce\Infrastructure\Models\CartItem;
use App\Modules\Commerce\Infrastructure\Models\Customer;
use App\Modules\Commerce\Infrastructure\Models\Order;
use App\Modules\Commerce\Presentation\Http\Requests\CartItemRequest;
use App\Modules\Commerce\Presentation\Http\Requests\CheckoutRequest;
use App\Modules\Commerce\Presentation\Http\Requests\StoreCustomerRequest;
use App\Modules\Commerce\Presentation\Http\Requests\UpdateCartItemRequest;
use App\Modules\Commerce\Presentation\Http\Requests\UpdateCustomerRequest;
use App\Modules\Commerce\Presentation\Http\Requests\UpdateOrderStatusRequest;
use App\Modules\Commerce\Presentation\Http\Resources\CartResource;
use App\Modules\Commerce\Presentation\Http\Resources\CustomerResource;
use App\Modules\Commerce\Presentation\Http\Resources\OrderResource;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Application\Services\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommerceController extends Controller
{
    public function __construct(private readonly AccessControlService $accessControl) {}

    public function getOrCreateCart(Request $request, string $businessSlug, GetOrCreateCartAction $action): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug, false);

        return ApiResponse::success(CartResource::make($action->handle($businessUnit, $request->input('session_token'))), 'Cart retrieved successfully');
    }

    public function getCart(string $businessSlug, string $sessionToken, GetCartAction $action): JsonResponse
    {
        return ApiResponse::success(CartResource::make($action->handle($this->publicBusinessUnit($businessSlug, false), $sessionToken)), 'Cart retrieved successfully');
    }

    public function addCartItem(CartItemRequest $request, string $businessSlug, string $sessionToken, GetCartAction $getCart, AddCartItemAction $action): JsonResponse
    {
        $cart = $getCart->handle($this->publicBusinessUnit($businessSlug, false), $sessionToken);

        return ApiResponse::success(CartResource::make($action->handle($cart, $request->validated())), 'Cart item added successfully', 201);
    }

    public function updateCartItem(UpdateCartItemRequest $request, string $businessSlug, string $sessionToken, CartItem $cartItem, GetCartAction $getCart, UpdateCartItemAction $action): JsonResponse
    {
        $cart = $getCart->handle($this->publicBusinessUnit($businessSlug, false), $sessionToken);
        abort_unless($cartItem->cart_id === $cart->id, 404);

        return ApiResponse::success(CartResource::make($action->handle($cart, $cartItem, $request->validated('quantity'))), 'Cart item updated successfully');
    }

    public function removeCartItem(string $businessSlug, string $sessionToken, CartItem $cartItem, GetCartAction $getCart, RemoveCartItemAction $action): JsonResponse
    {
        $cart = $getCart->handle($this->publicBusinessUnit($businessSlug, false), $sessionToken);
        abort_unless($cartItem->cart_id === $cart->id, 404);

        return ApiResponse::success(CartResource::make($action->handle($cart, $cartItem)), 'Cart item removed successfully');
    }

    public function clearCart(string $businessSlug, string $sessionToken, GetCartAction $getCart, ClearCartAction $action): JsonResponse
    {
        $cart = $getCart->handle($this->publicBusinessUnit($businessSlug, false), $sessionToken);

        return ApiResponse::success(CartResource::make($action->handle($cart)), 'Cart cleared successfully');
    }

    public function checkout(CheckoutRequest $request, string $businessSlug, GetCartAction $getCart, CreateOrderFromCartAction $action): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug, true);
        $cart = $getCart->handle($businessUnit, $request->validated('session_token'));

        return ApiResponse::success(new OrderResource($action->handle($businessUnit, $cart, $request->validated()), true), 'Order submitted successfully', 201);
    }

    public function publicOrder(Request $request, string $businessSlug, string $orderNumber): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug, false);
        $order = Order::query()
            ->with(['businessUnit', 'items'])
            ->where('business_unit_id', $businessUnit->id)
            ->where('order_number', $orderNumber)
            ->where('customer_phone', $request->query('phone'))
            ->firstOrFail();

        return ApiResponse::success(new OrderResource($order, true), 'Order retrieved successfully');
    }

    public function customers(Request $request, ListCustomersAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (Customer $customer) => CustomerResource::make($customer)->resolve()),
            'Customers retrieved successfully',
        );
    }

    public function storeCustomer(StoreCustomerRequest $request, CreateCustomerAction $action): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateCommerceScope($request, $data['business_unit_id'])) || ($error = $this->validatePriceList($data))) {
            return $error;
        }

        return ApiResponse::success(CustomerResource::make($action->handle($data)->load('businessUnit')), 'Customer created successfully', 201);
    }

    public function showCustomer(Request $request, Customer $customer, GetCustomerAction $action): JsonResponse
    {
        if ($error = $this->validateCommerceScope($request, $customer->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(CustomerResource::make($action->handle($customer)), 'Customer retrieved successfully');
    }

    public function updateCustomer(UpdateCustomerRequest $request, Customer $customer, UpdateCustomerAction $action): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateCommerceScope($request, $customer->business_unit_id)) || ($error = $this->validateCommerceScope($request, $data['business_unit_id'] ?? $customer->business_unit_id)) || ($error = $this->validatePriceList([...$data, 'business_unit_id' => $data['business_unit_id'] ?? $customer->business_unit_id]))) {
            return $error;
        }

        return ApiResponse::success(CustomerResource::make($action->handle($customer, $data)->load('businessUnit')), 'Customer updated successfully');
    }

    public function orders(Request $request, ListOrdersAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (Order $order) => OrderResource::make($order)->resolve()),
            'Orders retrieved successfully',
        );
    }

    public function showOrder(Request $request, Order $order, GetOrderAction $action): JsonResponse
    {
        if ($error = $this->validateCommerceScope($request, $order->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(OrderResource::make($action->handle($order)), 'Order retrieved successfully');
    }

    public function updateOrderStatus(UpdateOrderStatusRequest $request, Order $order, UpdateOrderStatusAction $action): JsonResponse
    {
        if ($error = $this->validateCommerceScope($request, $order->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(OrderResource::make($action->handle($order, $request->validated('status'), $request->validated('note'), $request->user())), 'Order status updated successfully');
    }

    public function cancelOrder(Request $request, Order $order, CancelOrderAction $action): JsonResponse
    {
        if ($error = $this->validateCommerceScope($request, $order->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(OrderResource::make($action->handle($order, $request->input('note', 'Order cancelled.'), $request->user())), 'Order cancelled successfully');
    }

    private function validateCommerceScope(Request $request, int|string $businessUnitId): ?JsonResponse
    {
        $businessUnit = BusinessUnit::query()->findOrFail($businessUnitId);
        if (! $this->accessControl->canAccessBusinessUnit($request->user(), $businessUnit) || ! $this->moduleEnabled($businessUnit, 'orders')) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return null;
    }

    private function validatePriceList(array $data): ?JsonResponse
    {
        if (empty($data['price_list_id'])) {
            return null;
        }

        return PriceList::query()->whereKey($data['price_list_id'])->where('business_unit_id', $data['business_unit_id'])->exists()
            ? null
            : ApiResponse::error('Price list must belong to the same business unit.', 422);
    }

    private function publicBusinessUnit(string $slug, bool $checkout): BusinessUnit
    {
        $businessUnit = BusinessUnit::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        abort_unless($this->moduleEnabled($businessUnit, 'products') && $this->moduleEnabled($businessUnit, 'orders'), 404);
        abort_if($checkout && ! $this->settingEnabled($businessUnit, 'checkout_enabled'), 403);
        abort_if($checkout && ! $this->settingEnabled($businessUnit, 'allow_guest_checkout'), 403);
        abort_if(! $this->settingEnabled($businessUnit, 'show_prices'), 403);

        return $businessUnit;
    }

    private function moduleEnabled(BusinessUnit $businessUnit, string $key): bool
    {
        return $businessUnit->moduleAssignments()->whereHas('activityModule', fn ($query) => $query->where('key', $key))->where('is_enabled', true)->exists();
    }

    private function settingEnabled(BusinessUnit $businessUnit, string $key): bool
    {
        $value = $businessUnit->settings()->where('key', $key)->value('value');
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return (bool) $value;
    }
}

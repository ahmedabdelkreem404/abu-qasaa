<?php

namespace App\Modules\ServicesRfq\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\ServicesRfq\Infrastructure\Models\RfqActivityLog;
use App\Modules\ServicesRfq\Infrastructure\Models\RfqQuotation;
use App\Modules\ServicesRfq\Infrastructure\Models\RfqRequest;
use App\Modules\ServicesRfq\Infrastructure\Models\Service;
use App\Modules\ServicesRfq\Presentation\Http\Resources\RfqQuotationResource;
use App\Modules\ServicesRfq\Presentation\Http\Resources\RfqRequestResource;
use App\Modules\ServicesRfq\Presentation\Http\Resources\ServiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicesRfqController extends Controller
{
    public function publicServices(string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $services = Service::query()->where('business_unit_id', $businessUnit->id)->where('status', 'published')->orderBy('sort_order')->get();

        return ApiResponse::success(ServiceResource::collection($services), 'Services retrieved successfully');
    }

    public function publicService(string $businessSlug, string $serviceSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $service = Service::query()->where('business_unit_id', $businessUnit->id)->where('slug', $serviceSlug)->where('status', 'published')->firstOrFail();

        return ApiResponse::success(ServiceResource::make($service), 'Service retrieved successfully');
    }

    public function submitRfq(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $data = $request->validate([
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'origin_country' => ['nullable', 'string', 'max:255'],
            'destination_country' => ['nullable', 'string', 'max:255'],
            'shipping_method' => ['nullable', 'string', 'max:255'],
            'incoterm' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'expected_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['required', 'string', 'max:255'],
            'items.*.target_price' => ['nullable', 'numeric', 'min:0'],
        ]);
        if (! empty($data['service_id']) && ! Service::query()->whereKey($data['service_id'])->where('business_unit_id', $businessUnit->id)->exists()) {
            return ApiResponse::error('Service must belong to the same business unit.', 422);
        }

        $rfq = DB::transaction(function () use ($businessUnit, $data): RfqRequest {
            $items = $data['items'];
            unset($data['items']);
            $number = $this->rfqNumber($businessUnit);
            $rfq = RfqRequest::query()->create($data + ['business_unit_id' => $businessUnit->id, 'number' => $number, 'rfq_number' => $number, 'status' => 'new', 'submitted_at' => now()]);
            foreach ($items as $item) {
                $rfq->items()->create($item + ['business_unit_id' => $businessUnit->id, 'service_id' => $rfq->service_id]);
            }
            RfqActivityLog::query()->create(['business_unit_id' => $businessUnit->id, 'rfq_request_id' => $rfq->id, 'event' => 'rfq_submitted', 'to_status' => 'new']);

            return $rfq->load('items');
        });

        return ApiResponse::success(new RfqRequestResource($rfq, true), 'RFQ submitted successfully', 201);
    }

    public function publicStatus(Request $request, string $businessSlug, string $rfqNumber): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $contact = $request->query('contact');
        $rfq = RfqRequest::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('rfq_number', $rfqNumber)
            ->where(fn ($query) => $query->where('phone', $contact)->orWhere('email', $contact))
            ->with('items')
            ->firstOrFail();

        return ApiResponse::success(new RfqRequestResource($rfq, true), 'RFQ status retrieved successfully');
    }

    public function rfqs(Request $request): JsonResponse
    {
        $query = $this->scopeQuery($request, RfqRequest::query()->with('items')->orderByDesc('id'));

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (RfqRequest $rfq) => RfqRequestResource::make($rfq)->resolve()), 'RFQs retrieved successfully');
    }

    public function createQuotation(Request $request, RfqRequest $rfqRequest): JsonResponse
    {
        if (! $this->canAccess($request, $rfqRequest->business_unit_id)) {
            return ApiResponse::error('Forbidden.', 403);
        }
        $data = $request->validate([
            'currency' => ['nullable', 'string', 'size:3'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
            'shipping_total' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.rfq_item_id' => ['nullable', 'integer', 'exists:rfq_items,id'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['required', 'string', 'max:255'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $quotation = DB::transaction(function () use ($request, $rfqRequest, $data): RfqQuotation {
            $subtotal = collect($data['items'])->sum(fn ($item) => (float) $item['quantity'] * (float) $item['unit_price']);
            $quotation = RfqQuotation::query()->create([
                'business_unit_id' => $rfqRequest->business_unit_id,
                'rfq_request_id' => $rfqRequest->id,
                'quotation_number' => 'QUO-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'status' => 'draft',
                'subtotal' => $subtotal,
                'tax_total' => $data['tax_total'] ?? 0,
                'shipping_total' => $data['shipping_total'] ?? 0,
                'grand_total' => $subtotal + ($data['tax_total'] ?? 0) + ($data['shipping_total'] ?? 0),
                'currency' => $data['currency'] ?? 'EGP',
                'created_by' => $request->user()?->id,
            ]);
            foreach ($data['items'] as $item) {
                $quotation->items()->create($item + ['subtotal' => (float) $item['quantity'] * (float) $item['unit_price']]);
            }
            RfqActivityLog::query()->create(['business_unit_id' => $rfqRequest->business_unit_id, 'rfq_request_id' => $rfqRequest->id, 'user_id' => $request->user()?->id, 'event' => 'quotation_created']);

            return $quotation->load('items');
        });

        return ApiResponse::success(RfqQuotationResource::make($quotation), 'Quotation created successfully', 201);
    }

    public function sendQuotation(Request $request, RfqQuotation $quotation): JsonResponse
    {
        if (! $this->canAccess($request, $quotation->business_unit_id)) {
            return ApiResponse::error('Forbidden.', 403);
        }
        $from = $quotation->status;
        $quotation->update(['status' => 'sent']);
        RfqActivityLog::query()->create(['business_unit_id' => $quotation->business_unit_id, 'rfq_request_id' => $quotation->rfq_request_id, 'user_id' => $request->user()?->id, 'event' => 'quotation_sent', 'from_status' => $from, 'to_status' => 'sent']);

        return ApiResponse::success(RfqQuotationResource::make($quotation), 'Quotation sent successfully');
    }

    private function publicBusinessUnit(string $slug): BusinessUnit
    {
        $businessUnit = BusinessUnit::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        abort_unless($businessUnit->moduleAssignments()->whereHas('activityModule', fn ($query) => $query->where('key', 'rfq'))->where('is_enabled', true)->exists(), 404);

        return $businessUnit;
    }

    private function scopeQuery(Request $request, $query)
    {
        if ($request->user()->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn('business_unit_id', $request->user()->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id'));
    }

    private function canAccess(Request $request, int $businessUnitId): bool
    {
        return $request->user()->isSuperAdmin() || $request->user()->businessUnitAssignments()->where('business_unit_id', $businessUnitId)->where('is_active', true)->exists();
    }

    private function rfqNumber(BusinessUnit $businessUnit): string
    {
        return 'RFQ-'.strtoupper(substr($businessUnit->slug, 0, 3)).'-'.now()->format('YmdHis').'-'.random_int(100, 999);
    }
}

<?php

namespace App\Modules\RealEstate\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Application\Services\AccessControlService;
use App\Modules\RealEstate\Infrastructure\Models\PropertyAppointment;
use App\Modules\RealEstate\Infrastructure\Models\PropertyReservation;
use App\Modules\RealEstate\Infrastructure\Models\PropertyUnit;
use App\Modules\RealEstate\Infrastructure\Models\RealEstateLead;
use App\Modules\RealEstate\Infrastructure\Models\RealEstateProject;
use App\Modules\RealEstate\Presentation\Http\Resources\PropertyAppointmentResource;
use App\Modules\RealEstate\Presentation\Http\Resources\PropertyReservationResource;
use App\Modules\RealEstate\Presentation\Http\Resources\PropertyUnitResource;
use App\Modules\RealEstate\Presentation\Http\Resources\RealEstateLeadResource;
use App\Modules\RealEstate\Presentation\Http\Resources\RealEstateProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealEstateController extends Controller
{
    public function __construct(private readonly AccessControlService $accessControl) {}

    public function publicProjects(string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $projects = RealEstateProject::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderBy('name_ar')
            ->get();

        return ApiResponse::success(RealEstateProjectResource::collection($projects), 'Projects retrieved successfully');
    }

    public function publicProject(string $businessSlug, string $projectSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $project = RealEstateProject::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('slug', $projectSlug)
            ->where('status', 'active')
            ->with(['units' => fn ($query) => $query->whereIn('status', ['available', 'reserved'])->orderBy('price')])
            ->firstOrFail();

        return ApiResponse::success(RealEstateProjectResource::make($project), 'Project retrieved successfully');
    }

    public function publicUnits(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $query = PropertyUnit::query()->where('business_unit_id', $businessUnit->id)->where('status', 'available')->orderBy('price');
        foreach (['project_id', 'unit_type', 'bedrooms', 'is_featured'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (PropertyUnit $unit) => PropertyUnitResource::make($unit)->resolve()), 'Units retrieved successfully');
    }

    public function submitLead(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $data = $request->validate($this->leadRules());
        if ($error = $this->validateRelations($businessUnit, $data['project_id'] ?? null, $data['unit_id'] ?? null)) {
            return $error;
        }

        $lead = RealEstateLead::query()->create($data + ['business_unit_id' => $businessUnit->id, 'status' => 'new']);

        return ApiResponse::success(RealEstateLeadResource::make($lead), 'Lead submitted successfully', 201);
    }

    public function submitViewing(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $data = $request->validate([
            'lead_id' => ['required', 'integer', 'exists:real_estate_leads,id'],
            'project_id' => ['nullable', 'integer', 'exists:real_estate_projects,id'],
            'unit_id' => ['nullable', 'integer', 'exists:property_units,id'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
        $lead = RealEstateLead::query()->whereKey($data['lead_id'])->where('business_unit_id', $businessUnit->id)->firstOrFail();
        if ($error = $this->validateRelations($businessUnit, $data['project_id'] ?? $lead->project_id, $data['unit_id'] ?? $lead->unit_id)) {
            return $error;
        }

        $appointment = PropertyAppointment::query()->create($data + ['business_unit_id' => $businessUnit->id, 'status' => 'scheduled']);

        return ApiResponse::success(PropertyAppointmentResource::make($appointment), 'Viewing request submitted successfully', 201);
    }

    public function submitReservationInterest(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:real_estate_projects,id'],
            'unit_id' => ['required', 'integer', 'exists:property_units,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);
        if ($error = $this->validateRelations($businessUnit, $data['project_id'], $data['unit_id'])) {
            return $error;
        }

        return DB::transaction(function () use ($businessUnit, $data): JsonResponse {
            $unit = PropertyUnit::query()->whereKey($data['unit_id'])->lockForUpdate()->firstOrFail();
            if (in_array($unit->status, ['reserved', 'sold'], true)) {
                return ApiResponse::error('Unit is not available for reservation.', 409);
            }
            $lead = RealEstateLead::query()->create([
                'business_unit_id' => $businessUnit->id,
                'project_id' => $data['project_id'],
                'unit_id' => $data['unit_id'],
                'source' => 'public_reservation_interest',
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => 'new',
            ]);
            $unit->update(['status' => 'reserved']);
            $reservation = PropertyReservation::query()->create([
                'business_unit_id' => $businessUnit->id,
                'lead_id' => $lead->id,
                'project_id' => $data['project_id'],
                'unit_id' => $data['unit_id'],
                'reservation_number' => $this->reservationNumber($businessUnit),
                'status' => 'pending',
                'currency' => $unit->currency,
                'reserved_at' => now(),
            ]);

            return ApiResponse::success(PropertyReservationResource::make($reservation), 'Reservation interest submitted successfully', 201);
        });
    }

    public function projects(Request $request): JsonResponse
    {
        $query = $this->scopeQuery($request, RealEstateProject::query()->orderByDesc('id'));

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (RealEstateProject $project) => RealEstateProjectResource::make($project)->resolve()), 'Projects retrieved successfully');
    }

    public function leads(Request $request): JsonResponse
    {
        $query = $this->scopeQuery($request, RealEstateLead::query()->orderByDesc('id'));

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (RealEstateLead $lead) => RealEstateLeadResource::make($lead)->resolve()), 'Leads retrieved successfully');
    }

    private function publicBusinessUnit(string $slug): BusinessUnit
    {
        $businessUnit = BusinessUnit::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        abort_unless($this->realEstateModuleEnabled($businessUnit), 404);

        return $businessUnit;
    }

    private function realEstateModuleEnabled(BusinessUnit $businessUnit): bool
    {
        return $businessUnit->moduleAssignments()
            ->whereHas('activityModule', fn ($query) => $query->where('key', 'real_estate_projects'))
            ->where('is_enabled', true)
            ->exists();
    }

    private function validateRelations(BusinessUnit $businessUnit, ?int $projectId, ?int $unitId): ?JsonResponse
    {
        if ($projectId && ! RealEstateProject::query()->whereKey($projectId)->where('business_unit_id', $businessUnit->id)->exists()) {
            return ApiResponse::error('Project must belong to the same business unit.', 422);
        }
        if ($unitId && ! PropertyUnit::query()->whereKey($unitId)->where('business_unit_id', $businessUnit->id)->where(fn ($query) => $projectId ? $query->where('project_id', $projectId) : $query)->exists()) {
            return ApiResponse::error('Unit must belong to the same business unit and project.', 422);
        }

        return null;
    }

    private function scopeQuery(Request $request, $query)
    {
        if ($request->user()->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn('business_unit_id', $request->user()->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id'));
    }

    private function leadRules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', 'exists:real_estate_projects,id'],
            'unit_id' => ['nullable', 'integer', 'exists:property_units,id'],
            'source' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'preferred_contact_method' => ['nullable', 'string', 'max:255'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'message' => ['nullable', 'string'],
        ];
    }

    private function reservationNumber(BusinessUnit $businessUnit): string
    {
        return 'RES-'.strtoupper(substr($businessUnit->slug, 0, 3)).'-'.now()->format('YmdHis').'-'.random_int(100, 999);
    }
}

<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Application\Actions\CreateBusinessUnitAction;
use App\Modules\BusinessUnits\Application\Actions\DeleteOrArchiveBusinessUnitAction;
use App\Modules\BusinessUnits\Application\Actions\ListBusinessUnitsAction;
use App\Modules\BusinessUnits\Application\Actions\SyncBusinessUnitModulesAction;
use App\Modules\BusinessUnits\Application\Actions\ToggleBusinessUnitStatusAction;
use App\Modules\BusinessUnits\Application\Actions\UpdateBusinessUnitAction;
use App\Modules\BusinessUnits\Application\Actions\UpdateBusinessUnitSettingsAction;
use App\Modules\BusinessUnits\Application\DTOs\BusinessUnitDTO;
use App\Modules\BusinessUnits\Infrastructure\Models\ActivityModule;
use App\Modules\BusinessUnits\Infrastructure\Models\ActivityTemplate;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\BusinessUnits\Infrastructure\Models\FeatureFlag;
use App\Modules\BusinessUnits\Presentation\Http\Requests\StoreBusinessUnitRequest;
use App\Modules\BusinessUnits\Presentation\Http\Requests\SyncBusinessUnitModulesRequest;
use App\Modules\BusinessUnits\Presentation\Http\Requests\UpdateBusinessUnitRequest;
use App\Modules\BusinessUnits\Presentation\Http\Requests\UpdateBusinessUnitSettingsRequest;
use App\Modules\BusinessUnits\Presentation\Http\Requests\UpdateFeatureFlagRequest;
use App\Modules\BusinessUnits\Presentation\Http\Resources\ActivityModuleResource;
use App\Modules\BusinessUnits\Presentation\Http\Resources\ActivityTemplateResource;
use App\Modules\BusinessUnits\Presentation\Http\Resources\BusinessUnitModuleResource;
use App\Modules\BusinessUnits\Presentation\Http\Resources\BusinessUnitResource;
use App\Modules\BusinessUnits\Presentation\Http\Resources\BusinessUnitSettingResource;
use App\Modules\BusinessUnits\Presentation\Http\Resources\FeatureFlagResource;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class BusinessUnitController extends Controller
{
    public function index(ListBusinessUnitsAction $action): JsonResponse
    {
        $paginator = $action->handle((int) request('per_page', 15));

        return ApiResponse::paginated(
            $paginator->through(fn (BusinessUnit $businessUnit) => BusinessUnitResource::make($businessUnit)->resolve()),
            'Business units retrieved successfully',
        );
    }

    public function store(StoreBusinessUnitRequest $request, CreateBusinessUnitAction $action): JsonResponse
    {
        $businessUnit = $action->handle(BusinessUnitDTO::fromArray($request->validated()));

        return ApiResponse::success(BusinessUnitResource::make($businessUnit), 'Business unit created successfully', 201);
    }

    public function show(BusinessUnit $businessUnit): JsonResponse
    {
        return ApiResponse::success(
            BusinessUnitResource::make($businessUnit->load(['moduleAssignments.activityModule', 'settings'])),
            'Business unit retrieved successfully',
        );
    }

    public function update(UpdateBusinessUnitRequest $request, BusinessUnit $businessUnit, UpdateBusinessUnitAction $action): JsonResponse
    {
        $businessUnit = $action->handle($businessUnit, $request->validated());

        return ApiResponse::success(BusinessUnitResource::make($businessUnit), 'Business unit updated successfully');
    }

    public function destroy(BusinessUnit $businessUnit, DeleteOrArchiveBusinessUnitAction $action): JsonResponse
    {
        $businessUnit = $action->handle($businessUnit);

        return ApiResponse::success(BusinessUnitResource::make($businessUnit), 'Business unit archived successfully');
    }

    public function toggleStatus(BusinessUnit $businessUnit, ToggleBusinessUnitStatusAction $action): JsonResponse
    {
        $businessUnit = $action->handle($businessUnit);

        return ApiResponse::success(BusinessUnitResource::make($businessUnit), 'Business unit status toggled successfully');
    }

    public function modules(BusinessUnit $businessUnit): JsonResponse
    {
        return ApiResponse::success(
            BusinessUnitModuleResource::collection($businessUnit->moduleAssignments()->with('activityModule')->get()),
            'Business unit modules retrieved successfully',
        );
    }

    public function updateModules(SyncBusinessUnitModulesRequest $request, BusinessUnit $businessUnit, SyncBusinessUnitModulesAction $action): JsonResponse
    {
        $businessUnit = $action->handle($businessUnit, $request->validated('modules'));

        return ApiResponse::success(
            BusinessUnitModuleResource::collection($businessUnit->moduleAssignments),
            'Business unit modules updated successfully',
        );
    }

    public function settings(BusinessUnit $businessUnit): JsonResponse
    {
        return ApiResponse::success(
            BusinessUnitSettingResource::collection($businessUnit->settings()->orderBy('key')->get()),
            'Business unit settings retrieved successfully',
        );
    }

    public function updateSettings(UpdateBusinessUnitSettingsRequest $request, BusinessUnit $businessUnit, UpdateBusinessUnitSettingsAction $action): JsonResponse
    {
        $businessUnit = $action->handle($businessUnit, $request->validated('settings'));

        return ApiResponse::success(
            BusinessUnitSettingResource::collection($businessUnit->settings),
            'Business unit settings updated successfully',
        );
    }

    public function activityTemplates(): JsonResponse
    {
        return ApiResponse::success(
            ActivityTemplateResource::collection(ActivityTemplate::query()->where('is_active', true)->orderBy('name')->get()),
            'Activity templates retrieved successfully',
        );
    }

    public function activityTemplate(ActivityTemplate $activityTemplate): JsonResponse
    {
        return ApiResponse::success(ActivityTemplateResource::make($activityTemplate), 'Activity template retrieved successfully');
    }

    public function activityModules(): JsonResponse
    {
        return ApiResponse::success(
            ActivityModuleResource::collection(ActivityModule::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get()),
            'Activity modules retrieved successfully',
        );
    }

    public function featureFlags(): JsonResponse
    {
        return ApiResponse::success(
            FeatureFlagResource::collection(FeatureFlag::query()->orderBy('business_unit_id')->orderBy('key')->get()),
            'Feature flags retrieved successfully',
        );
    }

    public function updateFeatureFlag(UpdateFeatureFlagRequest $request, FeatureFlag $featureFlag): JsonResponse
    {
        $featureFlag->update($request->validated());

        return ApiResponse::success(FeatureFlagResource::make($featureFlag->refresh()), 'Feature flag updated successfully');
    }

    public function publicIndex(): JsonResponse
    {
        return ApiResponse::success(
            BusinessUnitResource::collection(BusinessUnit::query()->where('status', 'active')->orderBy('name_en')->orderBy('name_ar')->get()),
            'Public business units retrieved successfully',
        );
    }

    public function publicShow(string $slug): JsonResponse
    {
        $businessUnit = BusinessUnit::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->with(['moduleAssignments.activityModule', 'settings'])
            ->firstOrFail();

        return ApiResponse::success(BusinessUnitResource::make($businessUnit), 'Public business unit retrieved successfully');
    }
}

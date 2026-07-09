<?php

namespace App\Modules\CMS\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\CMS\Application\Actions\ArchiveCmsPageAction;
use App\Modules\CMS\Application\Actions\CreateCmsPageAction;
use App\Modules\CMS\Application\Actions\GetPublicCmsPageBySlugAction;
use App\Modules\CMS\Application\Actions\ListCmsPagesAction;
use App\Modules\CMS\Application\Actions\ListContactInquiriesAction;
use App\Modules\CMS\Application\Actions\ListPublicCmsPagesAction;
use App\Modules\CMS\Application\Actions\PublishCmsPageAction;
use App\Modules\CMS\Application\Actions\SubmitContactInquiryAction;
use App\Modules\CMS\Application\Actions\UpdateCmsPageAction;
use App\Modules\CMS\Application\Actions\UpdateContactInquiryStatusAction;
use App\Modules\CMS\Application\Actions\UpsertCmsSectionsAction;
use App\Modules\CMS\Infrastructure\Models\CmsMenu;
use App\Modules\CMS\Infrastructure\Models\CmsMenuItem;
use App\Modules\CMS\Infrastructure\Models\CmsPage;
use App\Modules\CMS\Infrastructure\Models\ContactInquiry;
use App\Modules\CMS\Presentation\Http\Requests\StoreCmsPageRequest;
use App\Modules\CMS\Presentation\Http\Requests\SubmitContactInquiryRequest;
use App\Modules\CMS\Presentation\Http\Requests\UpdateCmsPageRequest;
use App\Modules\CMS\Presentation\Http\Requests\UpdateContactInquiryStatusRequest;
use App\Modules\CMS\Presentation\Http\Requests\UpsertCmsSectionsRequest;
use App\Modules\CMS\Presentation\Http\Resources\CmsMenuResource;
use App\Modules\CMS\Presentation\Http\Resources\CmsPageResource;
use App\Modules\CMS\Presentation\Http\Resources\ContactInquiryResource;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Application\Services\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function __construct(private readonly AccessControlService $accessControl) {}

    public function index(Request $request, ListCmsPagesAction $action): JsonResponse
    {
        $paginator = $action->handle($request->user(), $request->query());

        return ApiResponse::paginated(
            $paginator->through(fn (CmsPage $page) => CmsPageResource::make($page)->resolve()),
            'CMS pages retrieved successfully',
        );
    }

    public function store(StoreCmsPageRequest $request, CreateCmsPageAction $action): JsonResponse
    {
        if (! $this->canManageScope($request, $request->validated('business_unit_id'))) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(CmsPageResource::make($action->handle($request->validated(), $request->user())), 'CMS page created successfully', 201);
    }

    public function show(Request $request, CmsPage $cmsPage): JsonResponse
    {
        if (! $this->canManagePage($request, $cmsPage)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(CmsPageResource::make($cmsPage->load(['businessUnit', 'sections'])), 'CMS page retrieved successfully');
    }

    public function update(UpdateCmsPageRequest $request, CmsPage $cmsPage, UpdateCmsPageAction $action): JsonResponse
    {
        $businessUnitId = $request->validated('business_unit_id', $cmsPage->business_unit_id);
        if (! $this->canManagePage($request, $cmsPage) || ! $this->canManageScope($request, $businessUnitId)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(CmsPageResource::make($action->handle($cmsPage, $request->validated(), $request->user())), 'CMS page updated successfully');
    }

    public function destroy(Request $request, CmsPage $cmsPage, ArchiveCmsPageAction $action): JsonResponse
    {
        if (! $this->canManagePage($request, $cmsPage)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(CmsPageResource::make($action->handle($cmsPage)), 'CMS page archived successfully');
    }

    public function publish(Request $request, CmsPage $cmsPage, PublishCmsPageAction $action): JsonResponse
    {
        if (! $this->canManagePage($request, $cmsPage)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(CmsPageResource::make($action->handle($cmsPage)), 'CMS page published successfully');
    }

    public function upsertSections(Request $request, CmsPage $cmsPage, UpsertCmsSectionsRequest $sectionsRequest, UpsertCmsSectionsAction $action): JsonResponse
    {
        if (! $this->canManagePage($request, $cmsPage)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(CmsPageResource::make($action->handle($cmsPage, $sectionsRequest->validated('sections'))), 'CMS sections updated successfully');
    }

    public function menus(Request $request): JsonResponse
    {
        $query = CmsMenu::query()->with('items.children')->latest();
        if (! $request->user()->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $request->user()->businessUnitAssignments()->where('is_active', true)->select('business_unit_id'));
        }

        return ApiResponse::success(CmsMenuResource::collection($query->get()), 'CMS menus retrieved successfully');
    }

    public function storeMenu(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
        ]);

        if (! $this->canManageScope($request, $data['business_unit_id'] ?? null)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        $items = $data['items'] ?? [];
        unset($data['items']);

        $menu = CmsMenu::query()->create([...$data, 'is_active' => $data['is_active'] ?? true]);
        foreach ($items as $index => $item) {
            CmsMenuItem::query()->create([...$item, 'cms_menu_id' => $menu->id, 'sort_order' => $item['sort_order'] ?? $index]);
        }

        return ApiResponse::success(CmsMenuResource::make($menu->load('items.children')), 'CMS menu created successfully', 201);
    }

    public function showMenu(Request $request, CmsMenu $cmsMenu): JsonResponse
    {
        if (! $this->canManageScope($request, $cmsMenu->business_unit_id)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(CmsMenuResource::make($cmsMenu->load('items.children')), 'CMS menu retrieved successfully');
    }

    public function updateMenu(Request $request, CmsMenu $cmsMenu): JsonResponse
    {
        if (! $this->canManageScope($request, $cmsMenu->business_unit_id)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        $data = $request->validate([
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('business_unit_id', $data) && ! $this->canManageScope($request, $data['business_unit_id'])) {
            return ApiResponse::error('Forbidden.', 403);
        }

        $cmsMenu->update($data);

        return ApiResponse::success(CmsMenuResource::make($cmsMenu->refresh()->load('items.children')), 'CMS menu updated successfully');
    }

    public function destroyMenu(Request $request, CmsMenu $cmsMenu): JsonResponse
    {
        if (! $this->canManageScope($request, $cmsMenu->business_unit_id)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        $cmsMenu->delete();

        return ApiResponse::success(null, 'CMS menu deleted successfully');
    }

    public function inquiries(Request $request, ListContactInquiriesAction $action): JsonResponse
    {
        $paginator = $action->handle($request->user());

        return ApiResponse::paginated(
            $paginator->through(fn (ContactInquiry $inquiry) => ContactInquiryResource::make($inquiry)->resolve()),
            'Contact inquiries retrieved successfully',
        );
    }

    public function showInquiry(Request $request, ContactInquiry $contactInquiry): JsonResponse
    {
        if (! $this->canManageScope($request, $contactInquiry->business_unit_id)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(ContactInquiryResource::make($contactInquiry), 'Contact inquiry retrieved successfully');
    }

    public function updateInquiryStatus(UpdateContactInquiryStatusRequest $request, ContactInquiry $contactInquiry, UpdateContactInquiryStatusAction $action): JsonResponse
    {
        if (! $this->canManageScope($request, $contactInquiry->business_unit_id)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return ApiResponse::success(ContactInquiryResource::make($action->handle($contactInquiry, $request->validated('status'))), 'Contact inquiry status updated successfully');
    }

    public function publicPages(ListPublicCmsPagesAction $action): JsonResponse
    {
        return ApiResponse::success(CmsPageResource::collection($action->handle()), 'Published CMS pages retrieved successfully');
    }

    public function publicPage(string $slug, GetPublicCmsPageBySlugAction $action): JsonResponse
    {
        return ApiResponse::success(CmsPageResource::make($action->handle($slug)), 'Published CMS page retrieved successfully');
    }

    public function publicBusinessUnitPage(string $businessSlug): JsonResponse
    {
        $businessUnit = BusinessUnit::query()->where('slug', $businessSlug)->where('status', 'active')->firstOrFail();
        $page = CmsPage::query()
            ->with(['businessUnit', 'sections' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->where('business_unit_id', $businessUnit->id)
            ->where('status', 'published')
            ->where('page_type', 'business_unit_landing')
            ->firstOrFail();

        return ApiResponse::success(CmsPageResource::make($page), 'Business unit CMS page retrieved successfully');
    }

    public function publicMenu(string $location): JsonResponse
    {
        $menu = CmsMenu::query()->with('items.children')->where('location', $location)->where('is_active', true)->whereNull('business_unit_id')->firstOrFail();

        return ApiResponse::success(CmsMenuResource::make($menu), 'CMS menu retrieved successfully');
    }

    public function submitInquiry(SubmitContactInquiryRequest $request, SubmitContactInquiryAction $action): JsonResponse
    {
        return ApiResponse::success(ContactInquiryResource::make($action->handle($request->validated())), 'Contact inquiry submitted successfully', 201);
    }

    private function canManagePage(Request $request, CmsPage $page): bool
    {
        return $this->canManageScope($request, $page->business_unit_id);
    }

    private function canManageScope(Request $request, int|string|null $businessUnitId): bool
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($businessUnitId === null) {
            return false;
        }

        return $this->accessControl->canAccessBusinessUnit($user, $businessUnitId);
    }
}

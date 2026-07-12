<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Catalog\Application\Actions\ArchiveBrandAction;
use App\Modules\Catalog\Application\Actions\ArchiveCategoryAction;
use App\Modules\Catalog\Application\Actions\ArchivePriceListAction;
use App\Modules\Catalog\Application\Actions\ArchiveProductAction;
use App\Modules\Catalog\Application\Actions\CreateBrandAction;
use App\Modules\Catalog\Application\Actions\CreateCategoryAction;
use App\Modules\Catalog\Application\Actions\CreatePriceListAction;
use App\Modules\Catalog\Application\Actions\CreateProductAction;
use App\Modules\Catalog\Application\Actions\GetPublicProductBySlugAction;
use App\Modules\Catalog\Application\Actions\ListBrandsAction;
use App\Modules\Catalog\Application\Actions\ListCategoriesAction;
use App\Modules\Catalog\Application\Actions\ListPriceListsAction;
use App\Modules\Catalog\Application\Actions\ListProductsAction;
use App\Modules\Catalog\Application\Actions\ListPublicProductsAction;
use App\Modules\Catalog\Application\Actions\PublishProductAction;
use App\Modules\Catalog\Application\Actions\UpdateBrandAction;
use App\Modules\Catalog\Application\Actions\UpdateCategoryAction;
use App\Modules\Catalog\Application\Actions\UpdatePriceListAction;
use App\Modules\Catalog\Application\Actions\UpdateProductAction;
use App\Modules\Catalog\Application\Actions\UpsertProductImagesAction;
use App\Modules\Catalog\Application\Actions\UpsertProductPricesAction;
use App\Modules\Catalog\Application\Actions\UpsertProductVariantsAction;
use App\Modules\Catalog\Infrastructure\Models\Brand;
use App\Modules\Catalog\Infrastructure\Models\Category;
use App\Modules\Catalog\Infrastructure\Models\CorporateGiftInquiry;
use App\Modules\Catalog\Infrastructure\Models\PriceList;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductBadge;
use App\Modules\Catalog\Infrastructure\Models\ProductBundle;
use App\Modules\Catalog\Infrastructure\Models\ProductCollection;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
use App\Modules\Catalog\Presentation\Http\Requests\StoreBrandRequest;
use App\Modules\Catalog\Presentation\Http\Requests\StoreCategoryRequest;
use App\Modules\Catalog\Presentation\Http\Requests\StorePriceListRequest;
use App\Modules\Catalog\Presentation\Http\Requests\StoreProductRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpdateBrandRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpdateCategoryRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpdatePriceListRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpdateProductRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpsertProductImagesRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpsertProductPricesRequest;
use App\Modules\Catalog\Presentation\Http\Requests\UpsertProductVariantsRequest;
use App\Modules\Catalog\Presentation\Http\Resources\BrandResource;
use App\Modules\Catalog\Presentation\Http\Resources\CategoryResource;
use App\Modules\Catalog\Presentation\Http\Resources\CorporateGiftInquiryResource;
use App\Modules\Catalog\Presentation\Http\Resources\PriceListResource;
use App\Modules\Catalog\Presentation\Http\Resources\ProductBadgeResource;
use App\Modules\Catalog\Presentation\Http\Resources\ProductBundleResource;
use App\Modules\Catalog\Presentation\Http\Resources\ProductCollectionResource;
use App\Modules\Catalog\Presentation\Http\Resources\ProductResource;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Application\Services\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(private readonly AccessControlService $accessControl) {}

    public function categories(Request $request, ListCategoriesAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (Category $category) => CategoryResource::make($category)->resolve()),
            'Categories retrieved successfully',
        );
    }

    public function storeCategory(StoreCategoryRequest $request, CreateCategoryAction $action): JsonResponse
    {
        $data = $request->validated();
        if ($error = $this->validateCatalogScope($request, $data['business_unit_id'])) {
            return $error;
        }
        if ($error = $this->validateCategoryParent($data)) {
            return $error;
        }

        return ApiResponse::success(CategoryResource::make($action->handle($data)->load('businessUnit')), 'Category created successfully', 201);
    }

    public function showCategory(Request $request, Category $category): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $category->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(CategoryResource::make($category->load(['businessUnit', 'parent'])), 'Category retrieved successfully');
    }

    public function updateCategory(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action): JsonResponse
    {
        $data = $request->validated();
        $businessUnitId = $data['business_unit_id'] ?? $category->business_unit_id;
        if (($error = $this->validateCatalogScope($request, $category->business_unit_id)) || ($error = $this->validateCatalogScope($request, $businessUnitId)) || ($error = $this->validateCategoryParent([...$data, 'business_unit_id' => $businessUnitId]))) {
            return $error;
        }

        return ApiResponse::success(CategoryResource::make($action->handle($category, $data)->load(['businessUnit', 'parent'])), 'Category updated successfully');
    }

    public function destroyCategory(Request $request, Category $category, ArchiveCategoryAction $action): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $category->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(CategoryResource::make($action->handle($category)), 'Category archived successfully');
    }

    public function brands(Request $request, ListBrandsAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (Brand $brand) => BrandResource::make($brand)->resolve()),
            'Brands retrieved successfully',
        );
    }

    public function storeBrand(StoreBrandRequest $request, CreateBrandAction $action): JsonResponse
    {
        $data = $request->validated();
        if ($error = $this->validateCatalogScope($request, $data['business_unit_id'])) {
            return $error;
        }

        return ApiResponse::success(BrandResource::make($action->handle($data)->load('businessUnit')), 'Brand created successfully', 201);
    }

    public function showBrand(Request $request, Brand $brand): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $brand->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(BrandResource::make($brand->load('businessUnit')), 'Brand retrieved successfully');
    }

    public function updateBrand(UpdateBrandRequest $request, Brand $brand, UpdateBrandAction $action): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateCatalogScope($request, $brand->business_unit_id)) || ($error = $this->validateCatalogScope($request, $data['business_unit_id'] ?? $brand->business_unit_id))) {
            return $error;
        }

        return ApiResponse::success(BrandResource::make($action->handle($brand, $data)->load('businessUnit')), 'Brand updated successfully');
    }

    public function destroyBrand(Request $request, Brand $brand, ArchiveBrandAction $action): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $brand->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(BrandResource::make($action->handle($brand)), 'Brand archived successfully');
    }

    public function products(Request $request, ListProductsAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (Product $product) => ProductResource::make($product)->resolve()),
            'Products retrieved successfully',
        );
    }

    public function storeProduct(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateCatalogScope($request, $data['business_unit_id'])) || ($error = $this->validateProductRelations($data))) {
            return $error;
        }

        return ApiResponse::success(ProductResource::make($action->handle($data, $request->user())->load(['businessUnit', 'category', 'brand'])), 'Product created successfully', 201);
    }

    public function showProduct(Request $request, Product $product): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $product->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(ProductResource::make($product->load(['businessUnit', 'category', 'brand', 'variants', 'images', 'prices.priceList'])), 'Product retrieved successfully');
    }

    public function updateProduct(UpdateProductRequest $request, Product $product, UpdateProductAction $action): JsonResponse
    {
        $data = $request->validated();
        $businessUnitId = $data['business_unit_id'] ?? $product->business_unit_id;
        if (($error = $this->validateCatalogScope($request, $product->business_unit_id)) || ($error = $this->validateCatalogScope($request, $businessUnitId)) || ($error = $this->validateProductRelations([...$data, 'business_unit_id' => $businessUnitId]))) {
            return $error;
        }

        return ApiResponse::success(ProductResource::make($action->handle($product, $data, $request->user())->load(['businessUnit', 'category', 'brand'])), 'Product updated successfully');
    }

    public function destroyProduct(Request $request, Product $product, ArchiveProductAction $action): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $product->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(ProductResource::make($action->handle($product)), 'Product archived successfully');
    }

    public function publishProduct(Request $request, Product $product, PublishProductAction $action): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $product->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(ProductResource::make($action->handle($product)), 'Product published successfully');
    }

    public function upsertVariants(Request $request, Product $product, UpsertProductVariantsRequest $variantsRequest, UpsertProductVariantsAction $action): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $product->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(ProductResource::make($action->handle($product, $variantsRequest->validated('variants'))), 'Product variants updated successfully');
    }

    public function upsertImages(Request $request, Product $product, UpsertProductImagesRequest $imagesRequest, UpsertProductImagesAction $action): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $product->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(ProductResource::make($action->handle($product, $imagesRequest->validated('images'))), 'Product images updated successfully');
    }

    public function upsertPrices(Request $request, Product $product, UpsertProductPricesRequest $pricesRequest, UpsertProductPricesAction $action): JsonResponse
    {
        if (($error = $this->validateCatalogScope($request, $product->business_unit_id)) || ($error = $this->validateProductPrices($product, $pricesRequest->validated('prices')))) {
            return $error;
        }

        return ApiResponse::success(ProductResource::make($action->handle($product, $pricesRequest->validated('prices'))), 'Product prices updated successfully');
    }

    public function priceLists(Request $request, ListPriceListsAction $action): JsonResponse
    {
        return ApiResponse::paginated(
            $action->handle($request->user(), $request->query())->through(fn (PriceList $priceList) => PriceListResource::make($priceList)->resolve()),
            'Price lists retrieved successfully',
        );
    }

    public function storePriceList(StorePriceListRequest $request, CreatePriceListAction $action): JsonResponse
    {
        $data = $request->validated();
        if ($error = $this->validateCatalogScope($request, $data['business_unit_id'])) {
            return $error;
        }

        return ApiResponse::success(PriceListResource::make($action->handle($data)->load('businessUnit')), 'Price list created successfully', 201);
    }

    public function showPriceList(Request $request, PriceList $priceList): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $priceList->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(PriceListResource::make($priceList->load('businessUnit')), 'Price list retrieved successfully');
    }

    public function updatePriceList(UpdatePriceListRequest $request, PriceList $priceList, UpdatePriceListAction $action): JsonResponse
    {
        $data = $request->validated();
        if (($error = $this->validateCatalogScope($request, $priceList->business_unit_id)) || ($error = $this->validateCatalogScope($request, $data['business_unit_id'] ?? $priceList->business_unit_id))) {
            return $error;
        }

        return ApiResponse::success(PriceListResource::make($action->handle($priceList, $data)->load('businessUnit')), 'Price list updated successfully');
    }

    public function destroyPriceList(Request $request, PriceList $priceList, ArchivePriceListAction $action): JsonResponse
    {
        if ($error = $this->validateCatalogScope($request, $priceList->business_unit_id)) {
            return $error;
        }

        return ApiResponse::success(PriceListResource::make($action->handle($priceList)), 'Price list archived successfully');
    }

    public function publicProducts(Request $request, string $businessSlug, ListPublicProductsAction $action): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $showPrices = $this->showPrices($businessUnit);

        return ApiResponse::paginated(
            $action->handle($businessUnit, $request->query())->through(fn (Product $product) => (new ProductResource($product, true, $showPrices))->resolve()),
            'Public products retrieved successfully',
        );
    }

    public function publicProduct(string $businessSlug, string $productSlug, GetPublicProductBySlugAction $action): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);

        return ApiResponse::success(new ProductResource($action->handle($businessUnit, $productSlug), true, $this->showPrices($businessUnit)), 'Public product retrieved successfully');
    }

    public function publicCategories(string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);

        return ApiResponse::success(CategoryResource::collection(Category::query()->where('business_unit_id', $businessUnit->id)->where('status', 'active')->orderBy('sort_order')->get()), 'Public categories retrieved successfully');
    }

    public function publicBrands(string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);

        return ApiResponse::success(BrandResource::collection(Brand::query()->where('business_unit_id', $businessUnit->id)->where('status', 'active')->orderBy('sort_order')->get()), 'Public brands retrieved successfully');
    }

    public function publicCollections(string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);

        $collections = ProductCollection::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        return ApiResponse::success(ProductCollectionResource::collection($collections), 'Public collections retrieved successfully');
    }

    public function publicCollection(string $businessSlug, string $collectionSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $collection = ProductCollection::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('slug', $collectionSlug)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->with(['items.product' => fn ($query) => $query
                ->where('business_unit_id', $businessUnit->id)
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->with(['category', 'brand', 'images', 'badges', 'bundle.items.childProduct'])])
            ->firstOrFail();

        return ApiResponse::success(new ProductCollectionResource($collection, true, $this->showPrices($businessUnit)), 'Public collection retrieved successfully');
    }

    public function publicFeaturedProducts(Request $request, string $businessSlug): JsonResponse
    {
        return $this->publicMerchandisedProducts($request, $businessSlug, fn ($query) => $query->where('is_featured', true), 'Featured products retrieved successfully');
    }

    public function publicGiftProducts(Request $request, string $businessSlug): JsonResponse
    {
        return $this->publicMerchandisedProducts($request, $businessSlug, fn ($query) => $query->where('gift_options_json->is_gift_product', true), 'Gift products retrieved successfully');
    }

    public function publicSeasonalProducts(Request $request, string $businessSlug): JsonResponse
    {
        return $this->publicMerchandisedProducts($request, $businessSlug, fn ($query) => $query->where('merchandising_json->seasonal', true), 'Seasonal products retrieved successfully');
    }

    public function publicCorporateGiftProducts(Request $request, string $businessSlug): JsonResponse
    {
        return $this->publicMerchandisedProducts($request, $businessSlug, fn ($query) => $query->where('gift_options_json->corporate_gift_available', true), 'Corporate gift products retrieved successfully');
    }

    public function submitCorporateGiftInquiry(Request $request, string $businessSlug): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $data = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'product_collection_id' => ['nullable', 'integer', 'exists:product_collections,id'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'budget_range' => ['nullable', 'string', 'max:255'],
            'occasion' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);
        if (($error = $this->validateOptionalProductScope($businessUnit, $data['product_id'] ?? null)) || ($error = $this->validateOptionalCollectionScope($businessUnit, $data['product_collection_id'] ?? null))) {
            return $error;
        }

        $inquiry = CorporateGiftInquiry::query()->create($data + ['business_unit_id' => $businessUnit->id, 'status' => 'new']);

        return ApiResponse::success(CorporateGiftInquiryResource::make($inquiry), 'Corporate gift inquiry submitted successfully', 201);
    }

    public function collections(Request $request): JsonResponse
    {
        $query = ProductCollection::query()->with('businessUnit')->orderBy('sort_order');
        $query = $this->scopeQueryToUserBusinessUnits($request, $query);

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (ProductCollection $collection) => ProductCollectionResource::make($collection)->resolve()), 'Collections retrieved successfully');
    }

    public function storeCollection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_unit_id' => ['required', 'integer', 'exists:business_units,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,draft,archived'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_description_ar' => ['nullable', 'string'],
            'seo_description_en' => ['nullable', 'string'],
        ]);
        if ($error = $this->validateCatalogScope($request, $data['business_unit_id'])) {
            return $error;
        }

        $collection = ProductCollection::query()->create($data);

        return ApiResponse::success(ProductCollectionResource::make($collection), 'Collection created successfully', 201);
    }

    public function badges(Request $request): JsonResponse
    {
        $query = ProductBadge::query()->orderBy('sort_order');
        $query = $this->scopeQueryToUserBusinessUnits($request, $query);

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (ProductBadge $badge) => ProductBadgeResource::make($badge)->resolve()), 'Badges retrieved successfully');
    }

    public function storeBadge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_unit_id' => ['required', 'integer', 'exists:business_units,id'],
            'key' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);
        if ($error = $this->validateCatalogScope($request, $data['business_unit_id'])) {
            return $error;
        }

        return ApiResponse::success(ProductBadgeResource::make(ProductBadge::query()->create($data)), 'Badge created successfully', 201);
    }

    public function bundles(Request $request): JsonResponse
    {
        $query = ProductBundle::query()->with(['product', 'items.childProduct'])->orderByDesc('id');
        $query = $this->scopeQueryToUserBusinessUnits($request, $query);

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (ProductBundle $bundle) => ProductBundleResource::make($bundle)->resolve()), 'Bundles retrieved successfully');
    }

    public function storeBundle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_unit_id' => ['required', 'integer', 'exists:business_units,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'bundle_type' => ['required', 'in:fixed_box,corporate_box,seasonal_box,simple_bundle'],
            'pricing_mode' => ['required', 'in:use_parent_product_price,fixed_bundle_price'],
            'fixed_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'metadata_json' => ['nullable', 'array'],
        ]);
        if (($error = $this->validateCatalogScope($request, $data['business_unit_id'])) || ($error = $this->validateOptionalProductScope(BusinessUnit::query()->findOrFail($data['business_unit_id']), $data['product_id']))) {
            return $error;
        }

        return ApiResponse::success(ProductBundleResource::make(ProductBundle::query()->create($data)), 'Bundle created successfully', 201);
    }

    public function corporateGiftInquiries(Request $request): JsonResponse
    {
        $query = CorporateGiftInquiry::query()->orderByDesc('id');
        $query = $this->scopeQueryToUserBusinessUnits($request, $query);

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (CorporateGiftInquiry $inquiry) => CorporateGiftInquiryResource::make($inquiry)->resolve()), 'Corporate gift inquiries retrieved successfully');
    }

    private function validateCatalogScope(Request $request, int|string $businessUnitId): ?JsonResponse
    {
        $businessUnit = BusinessUnit::query()->findOrFail($businessUnitId);
        if (! $this->accessControl->canAccessBusinessUnit($request->user(), $businessUnit) || ! $this->productsModuleEnabled($businessUnit)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return null;
    }

    private function validateCategoryParent(array $data): ?JsonResponse
    {
        if (empty($data['parent_id'])) {
            return null;
        }

        return Category::query()->whereKey($data['parent_id'])->where('business_unit_id', $data['business_unit_id'])->exists()
            ? null
            : ApiResponse::error('Parent category must belong to the same business unit.', 422);
    }

    private function validateProductRelations(array $data): ?JsonResponse
    {
        if (! empty($data['category_id']) && ! Category::query()->whereKey($data['category_id'])->where('business_unit_id', $data['business_unit_id'])->exists()) {
            return ApiResponse::error('Category must belong to the same business unit.', 422);
        }
        if (! empty($data['brand_id']) && ! Brand::query()->whereKey($data['brand_id'])->where('business_unit_id', $data['business_unit_id'])->exists()) {
            return ApiResponse::error('Brand must belong to the same business unit.', 422);
        }

        return null;
    }

    private function validateProductPrices(Product $product, array $prices): ?JsonResponse
    {
        foreach ($prices as $price) {
            if (! PriceList::query()->whereKey($price['price_list_id'])->where('business_unit_id', $product->business_unit_id)->exists()) {
                return ApiResponse::error('Price list must belong to the same business unit as the product.', 422);
            }
            if (! empty($price['product_variant_id']) && ! ProductVariant::query()->whereKey($price['product_variant_id'])->where('product_id', $product->id)->exists()) {
                return ApiResponse::error('Product variant must belong to the product.', 422);
            }
        }

        return null;
    }

    private function validateOptionalProductScope(BusinessUnit $businessUnit, ?int $productId): ?JsonResponse
    {
        if ($productId === null) {
            return null;
        }

        return Product::query()->whereKey($productId)->where('business_unit_id', $businessUnit->id)->exists()
            ? null
            : ApiResponse::error('Product must belong to the same business unit.', 422);
    }

    private function validateOptionalCollectionScope(BusinessUnit $businessUnit, ?int $collectionId): ?JsonResponse
    {
        if ($collectionId === null) {
            return null;
        }

        return ProductCollection::query()->whereKey($collectionId)->where('business_unit_id', $businessUnit->id)->exists()
            ? null
            : ApiResponse::error('Collection must belong to the same business unit.', 422);
    }

    private function publicMerchandisedProducts(Request $request, string $businessSlug, callable $constraint, string $message): JsonResponse
    {
        $businessUnit = $this->publicBusinessUnit($businessSlug);
        $query = Product::query()
            ->where('business_unit_id', $businessUnit->id)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->with(['category', 'brand', 'images', 'badges', 'bundle.items.childProduct'])
            ->orderByDesc('is_featured')
            ->orderBy('name_ar');
        $constraint($query);

        return ApiResponse::paginated(
            $query->paginate((int) $request->query('per_page', 15))->through(fn (Product $product) => (new ProductResource($product, true, $this->showPrices($businessUnit)))->resolve()),
            $message,
        );
    }

    private function scopeQueryToUserBusinessUnits(Request $request, $query)
    {
        if ($request->user()->isSuperAdmin()) {
            return $query;
        }

        $ids = $request->user()->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id');

        return $query->whereIn('business_unit_id', $ids);
    }

    private function publicBusinessUnit(string $slug): BusinessUnit
    {
        $businessUnit = BusinessUnit::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();
        abort_unless($this->productsModuleEnabled($businessUnit), 404);

        return $businessUnit;
    }

    private function showPrices(BusinessUnit $businessUnit): bool
    {
        $setting = $businessUnit->settings()->where('key', 'show_prices')->value('value');
        if (is_string($setting)) {
            $decoded = json_decode($setting, true);
            $setting = json_last_error() === JSON_ERROR_NONE ? $decoded : $setting;
        }

        return $setting === null ? true : (bool) $setting;
    }

    private function productsModuleEnabled(BusinessUnit $businessUnit): bool
    {
        return $businessUnit->moduleAssignments()
            ->whereHas('activityModule', fn ($query) => $query->where('key', 'products'))
            ->where('is_enabled', true)
            ->exists();
    }
}

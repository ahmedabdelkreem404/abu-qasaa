import type {
  ActivityModule,
  ActivityTemplate,
  ApiResponse,
  Brand,
  BrandStatus,
  BusinessUnit,
  BusinessUnitModule,
  BusinessUnitSetting,
  Cart,
  Category,
  CategoryStatus,
  CmsMenu,
  CmsPage,
  CmsPageStatus,
  CmsPageType,
  CmsSection,
  ContactInquiry,
  Customer,
  CustomerType,
  InquiryStatus,
  FeatureFlag,
  LoginResponse,
  PaginatedResponse,
  PriceList,
  PriceListType,
  Product,
  ProductBadge,
  ProductBundle,
  ProductCollection,
  CorporateGiftInquiry,
  ProductImage,
  ProductPrice,
  ProductStatus,
  ProductType,
  ProductVariant,
  ProductVisibility,
  Order,
  OrderStatus,
  ManualPaymentProof,
  Payment,
  PaymentMethod,
  PaymentMethodType,
  PaymentTransaction,
  PaymobInitiationResponse,
  PublicPaymentStatus,
  PublicOrderPaymentOptions,
  Branch,
  Warehouse,
  StockItem,
  StockMovement,
  StockTransfer,
  InventorySummary,
  PublicAvailability,
  WholesaleAccess,
  WholesaleApplication,
  WholesaleCustomer,
  WholesalePricing,
  RealEstateProject,
  PropertyUnit,
  RealEstateLead,
  ImportExportService,
  RfqRequest,
} from "@/types/platform";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";
const TOKEN_KEY = "abu_qasaa_auth_token";

export function getStoredToken() {
  if (typeof window === "undefined") {
    return null;
  }

  return window.localStorage.getItem(TOKEN_KEY);
}

export function setStoredToken(token: string) {
  window.localStorage.setItem(TOKEN_KEY, token);
}

export function clearStoredToken() {
  window.localStorage.removeItem(TOKEN_KEY);
}

export async function apiRequest<T>(
  path: string,
  init?: RequestInit,
): Promise<T> {
  const token = getStoredToken();
  const response = await fetch(`${API_URL}${path}`, {
    ...init,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...init?.headers,
    },
  });

  if (!response.ok) {
    const error = new Error(`API request failed with ${response.status}`);
    error.name = String(response.status);
    throw error;
  }

  return response.json() as Promise<T>;
}

export async function login(email: string, password: string) {
  const response = await apiRequest<ApiResponse<LoginResponse>>("/auth/login", {
    method: "POST",
    body: JSON.stringify({ email, password }),
  });
  setStoredToken(response.data.token);

  return response;
}

export async function logout() {
  try {
    await apiRequest<ApiResponse<null>>("/auth/logout", { method: "POST" });
  } finally {
    clearStoredToken();
  }
}

export async function getCurrentUser() {
  return apiRequest<ApiResponse<LoginResponse["user"]>>("/auth/me");
}

export async function getCurrentUserPermissions() {
  const response = await getCurrentUser();
  return response.data.permissions;
}

export async function getCurrentUserBusinessUnits() {
  const response = await getCurrentUser();
  return response.data.business_units;
}

export type BusinessUnitPayload = {
  parent_id?: number | null;
  name_ar: string;
  name_en?: string | null;
  slug: string;
  type: string;
  status: string;
  description?: string | null;
  primary_color?: string | null;
  secondary_color?: string | null;
  template_key?: string | null;
};

export async function listBusinessUnits() {
  return apiRequest<PaginatedResponse<BusinessUnit>>("/business-units");
}

export async function getBusinessUnit(id: string | number) {
  return apiRequest<ApiResponse<BusinessUnit>>(`/business-units/${id}`);
}

export async function createBusinessUnit(payload: BusinessUnitPayload) {
  return apiRequest<ApiResponse<BusinessUnit>>("/business-units", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function updateBusinessUnit(
  id: string | number,
  payload: Partial<BusinessUnitPayload>,
) {
  return apiRequest<ApiResponse<BusinessUnit>>(`/business-units/${id}`, {
    method: "PATCH",
    body: JSON.stringify(payload),
  });
}

export async function deleteBusinessUnit(id: string | number) {
  return apiRequest<ApiResponse<BusinessUnit>>(`/business-units/${id}`, {
    method: "DELETE",
  });
}

export async function toggleBusinessUnitStatus(id: string | number) {
  return apiRequest<ApiResponse<BusinessUnit>>(
    `/business-units/${id}/toggle-status`,
    { method: "POST" },
  );
}

export async function getActivityTemplates() {
  return apiRequest<ApiResponse<ActivityTemplate[]>>("/activity-templates");
}

export async function getActivityModules() {
  return apiRequest<ApiResponse<ActivityModule[]>>("/activity-modules");
}

export async function getBusinessUnitModules(id: string | number) {
  return apiRequest<ApiResponse<BusinessUnitModule[]>>(
    `/business-units/${id}/modules`,
  );
}

export async function updateBusinessUnitModules(
  id: string | number,
  modules: Array<{ key: string; is_enabled: boolean }>,
) {
  return apiRequest<ApiResponse<BusinessUnitModule[]>>(
    `/business-units/${id}/modules`,
    {
      method: "PUT",
      body: JSON.stringify({ modules }),
    },
  );
}

export async function getBusinessUnitSettings(id: string | number) {
  return apiRequest<ApiResponse<BusinessUnitSetting[]>>(
    `/business-units/${id}/settings`,
  );
}

export async function updateBusinessUnitSettings(
  id: string | number,
  settings: Record<string, unknown>,
) {
  return apiRequest<ApiResponse<BusinessUnitSetting[]>>(
    `/business-units/${id}/settings`,
    {
      method: "PUT",
      body: JSON.stringify({ settings }),
    },
  );
}

export async function listFeatureFlags() {
  return apiRequest<ApiResponse<FeatureFlag[]>>("/feature-flags");
}

export async function updateFeatureFlag(id: string | number, value: boolean) {
  return apiRequest<ApiResponse<FeatureFlag>>(`/feature-flags/${id}`, {
    method: "PUT",
    body: JSON.stringify({ value }),
  });
}

export async function listPublicBusinessUnits() {
  return apiRequest<ApiResponse<BusinessUnit[]>>("/public/business-units");
}

export async function getPublicBusinessUnitBySlug(slug: string) {
  return apiRequest<ApiResponse<BusinessUnit>>(`/public/business-units/${slug}`);
}

export type CmsPagePayload = Partial<CmsPage> & {
  title_ar: string;
  slug: string;
  page_type: CmsPageType;
  status: CmsPageStatus;
};

export async function listCmsPages(params?: URLSearchParams) {
  const query = params ? `?${params.toString()}` : "";
  return apiRequest<PaginatedResponse<CmsPage>>(`/cms/pages${query}`);
}

export async function getCmsPage(id: string | number) {
  return apiRequest<ApiResponse<CmsPage>>(`/cms/pages/${id}`);
}

export async function createCmsPage(payload: CmsPagePayload) {
  return apiRequest<ApiResponse<CmsPage>>("/cms/pages", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function updateCmsPage(id: string | number, payload: Partial<CmsPagePayload>) {
  return apiRequest<ApiResponse<CmsPage>>(`/cms/pages/${id}`, {
    method: "PATCH",
    body: JSON.stringify(payload),
  });
}

export async function deleteCmsPage(id: string | number) {
  return apiRequest<ApiResponse<CmsPage>>(`/cms/pages/${id}`, { method: "DELETE" });
}

export async function publishCmsPage(id: string | number) {
  return apiRequest<ApiResponse<CmsPage>>(`/cms/pages/${id}/publish`, { method: "POST" });
}

export async function updateCmsPageSections(id: string | number, sections: CmsSection[]) {
  return apiRequest<ApiResponse<CmsPage>>(`/cms/pages/${id}/sections`, {
    method: "PUT",
    body: JSON.stringify({ sections }),
  });
}

export async function listPublicCmsPages() {
  return apiRequest<ApiResponse<CmsPage[]>>("/public/cms/pages");
}

export async function getPublicCmsPageBySlug(slug: string) {
  return apiRequest<ApiResponse<CmsPage>>(`/public/cms/pages/${slug}`);
}

export async function getBusinessUnitPublicCmsPage(slug: string) {
  return apiRequest<ApiResponse<CmsPage>>(`/public/cms/business-units/${slug}/page`);
}

export async function getMenuByLocation(location: string) {
  return apiRequest<ApiResponse<CmsMenu>>(`/public/cms/menus/${location}`);
}

export async function submitContactInquiry(payload: {
  business_unit_id?: number | null;
  name: string;
  email?: string;
  phone?: string;
  subject?: string;
  message: string;
  source_page?: string;
}) {
  return apiRequest<ApiResponse<ContactInquiry>>("/public/contact-inquiries", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function listContactInquiries() {
  return apiRequest<PaginatedResponse<ContactInquiry>>("/cms/contact-inquiries");
}

export async function updateContactInquiryStatus(id: string | number, status: InquiryStatus) {
  return apiRequest<ApiResponse<ContactInquiry>>(`/cms/contact-inquiries/${id}/status`, {
    method: "PUT",
    body: JSON.stringify({ status }),
  });
}

export type CategoryPayload = Partial<Category> & {
  business_unit_id: number;
  name_ar: string;
  slug: string;
  status: CategoryStatus;
};

export type BrandPayload = Partial<Brand> & {
  business_unit_id: number;
  name_ar: string;
  slug: string;
  status: BrandStatus;
};

export type ProductPayload = Partial<Product> & {
  business_unit_id: number;
  name_ar: string;
  slug: string;
  product_type: ProductType;
  status: ProductStatus;
  visibility: ProductVisibility;
};

export type PriceListPayload = Partial<PriceList> & {
  business_unit_id: number;
  name: string;
  key: string;
  type: PriceListType;
};

function withQuery(path: string, params?: URLSearchParams) {
  return params ? `${path}?${params.toString()}` : path;
}

export async function listCategories(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Category>>(withQuery("/catalog/categories", params));
}

export async function getCategory(id: string | number) {
  return apiRequest<ApiResponse<Category>>(`/catalog/categories/${id}`);
}

export async function createCategory(payload: CategoryPayload) {
  return apiRequest<ApiResponse<Category>>("/catalog/categories", { method: "POST", body: JSON.stringify(payload) });
}

export async function updateCategory(id: string | number, payload: Partial<CategoryPayload>) {
  return apiRequest<ApiResponse<Category>>(`/catalog/categories/${id}`, { method: "PATCH", body: JSON.stringify(payload) });
}

export async function deleteCategory(id: string | number) {
  return apiRequest<ApiResponse<Category>>(`/catalog/categories/${id}`, { method: "DELETE" });
}

export async function listBrands(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Brand>>(withQuery("/catalog/brands", params));
}

export async function getBrand(id: string | number) {
  return apiRequest<ApiResponse<Brand>>(`/catalog/brands/${id}`);
}

export async function createBrand(payload: BrandPayload) {
  return apiRequest<ApiResponse<Brand>>("/catalog/brands", { method: "POST", body: JSON.stringify(payload) });
}

export async function updateBrand(id: string | number, payload: Partial<BrandPayload>) {
  return apiRequest<ApiResponse<Brand>>(`/catalog/brands/${id}`, { method: "PATCH", body: JSON.stringify(payload) });
}

export async function deleteBrand(id: string | number) {
  return apiRequest<ApiResponse<Brand>>(`/catalog/brands/${id}`, { method: "DELETE" });
}

export async function listProducts(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Product>>(withQuery("/catalog/products", params));
}

export async function getProduct(id: string | number) {
  return apiRequest<ApiResponse<Product>>(`/catalog/products/${id}`);
}

export async function createProduct(payload: ProductPayload) {
  return apiRequest<ApiResponse<Product>>("/catalog/products", { method: "POST", body: JSON.stringify(payload) });
}

export async function updateProduct(id: string | number, payload: Partial<ProductPayload>) {
  return apiRequest<ApiResponse<Product>>(`/catalog/products/${id}`, { method: "PATCH", body: JSON.stringify(payload) });
}

export async function deleteProduct(id: string | number) {
  return apiRequest<ApiResponse<Product>>(`/catalog/products/${id}`, { method: "DELETE" });
}

export async function publishProduct(id: string | number) {
  return apiRequest<ApiResponse<Product>>(`/catalog/products/${id}/publish`, { method: "POST" });
}

export async function updateProductVariants(id: string | number, variants: ProductVariant[]) {
  return apiRequest<ApiResponse<Product>>(`/catalog/products/${id}/variants`, { method: "PUT", body: JSON.stringify({ variants }) });
}

export async function updateProductImages(id: string | number, images: ProductImage[]) {
  return apiRequest<ApiResponse<Product>>(`/catalog/products/${id}/images`, { method: "PUT", body: JSON.stringify({ images }) });
}

export async function updateProductPrices(id: string | number, prices: ProductPrice[]) {
  return apiRequest<ApiResponse<Product>>(`/catalog/products/${id}/prices`, { method: "PUT", body: JSON.stringify({ prices }) });
}

export async function listPriceLists(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<PriceList>>(withQuery("/catalog/price-lists", params));
}

export async function getPriceList(id: string | number) {
  return apiRequest<ApiResponse<PriceList>>(`/catalog/price-lists/${id}`);
}

export async function createPriceList(payload: PriceListPayload) {
  return apiRequest<ApiResponse<PriceList>>("/catalog/price-lists", { method: "POST", body: JSON.stringify(payload) });
}

export async function updatePriceList(id: string | number, payload: Partial<PriceListPayload>) {
  return apiRequest<ApiResponse<PriceList>>(`/catalog/price-lists/${id}`, { method: "PATCH", body: JSON.stringify(payload) });
}

export async function deletePriceList(id: string | number) {
  return apiRequest<ApiResponse<PriceList>>(`/catalog/price-lists/${id}`, { method: "DELETE" });
}

export async function listPublicProducts(businessSlug: string, params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Product>>(withQuery(`/public/${businessSlug}/products`, params));
}

export async function getPublicProductBySlug(businessSlug: string, productSlug: string) {
  return apiRequest<ApiResponse<Product>>(`/public/${businessSlug}/products/${productSlug}`);
}

export async function getPublicProductAvailability(businessSlug: string, productSlug: string) {
  return apiRequest<ApiResponse<PublicAvailability>>(`/public/${businessSlug}/products/${productSlug}/availability`);
}

export async function listPublicBranches(businessSlug: string) {
  return apiRequest<ApiResponse<Branch[]>>(`/public/${businessSlug}/branches`);
}

export async function listPublicCategories(businessSlug: string) {
  return apiRequest<ApiResponse<Category[]>>(`/public/${businessSlug}/categories`);
}

export async function listPublicBrands(businessSlug: string) {
  return apiRequest<ApiResponse<Brand[]>>(`/public/${businessSlug}/brands`);
}

export async function listPublicCollections(businessSlug: string) {
  return apiRequest<ApiResponse<ProductCollection[]>>(`/public/${businessSlug}/collections`);
}

export async function getPublicCollection(businessSlug: string, collectionSlug: string) {
  return apiRequest<ApiResponse<ProductCollection>>(`/public/${businessSlug}/collections/${collectionSlug}`);
}

export async function listPublicFeaturedProducts(businessSlug: string) {
  return apiRequest<PaginatedResponse<Product>>(`/public/${businessSlug}/featured-products`);
}

export async function listPublicGiftProducts(businessSlug: string) {
  return apiRequest<PaginatedResponse<Product>>(`/public/${businessSlug}/gift-products`);
}

export async function listPublicSeasonalProducts(businessSlug: string) {
  return apiRequest<PaginatedResponse<Product>>(`/public/${businessSlug}/seasonal-products`);
}

export async function listPublicCorporateGiftProducts(businessSlug: string) {
  return apiRequest<PaginatedResponse<Product>>(`/public/${businessSlug}/corporate-gift-products`);
}

export async function submitCorporateGiftInquiry(businessSlug: string, payload: Partial<CorporateGiftInquiry>) {
  return apiRequest<ApiResponse<CorporateGiftInquiry>>(`/public/${businessSlug}/corporate-gift-inquiries`, {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function listProductCollections(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<ProductCollection>>(withQuery("/catalog/collections", params));
}

export async function listProductBadges(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<ProductBadge>>(withQuery("/catalog/badges", params));
}

export async function listProductBundles(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<ProductBundle>>(withQuery("/catalog/bundles", params));
}

export async function listCorporateGiftInquiries(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<CorporateGiftInquiry>>(withQuery("/catalog/corporate-gift-inquiries", params));
}

export async function getOrCreateCart(businessSlug: string, session_token?: string | null) {
  return apiRequest<ApiResponse<Cart>>(`/public/${businessSlug}/cart`, {
    method: "POST",
    body: JSON.stringify({ session_token }),
  });
}

export async function getCart(businessSlug: string, sessionToken: string) {
  return apiRequest<ApiResponse<Cart>>(`/public/${businessSlug}/cart/${sessionToken}`);
}

export async function addCartItem(businessSlug: string, sessionToken: string, payload: { product_id: number; product_variant_id?: number | null; quantity: number; wholesale_phone?: string | null; wholesale_token?: string | null }) {
  return apiRequest<ApiResponse<Cart>>(`/public/${businessSlug}/cart/${sessionToken}/items`, {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function updateCartItem(businessSlug: string, sessionToken: string, itemId: string | number, quantity: number) {
  return apiRequest<ApiResponse<Cart>>(`/public/${businessSlug}/cart/${sessionToken}/items/${itemId}`, {
    method: "PUT",
    body: JSON.stringify({ quantity }),
  });
}

export async function removeCartItem(businessSlug: string, sessionToken: string, itemId: string | number) {
  return apiRequest<ApiResponse<Cart>>(`/public/${businessSlug}/cart/${sessionToken}/items/${itemId}`, { method: "DELETE" });
}

export async function clearCart(businessSlug: string, sessionToken: string) {
  return apiRequest<ApiResponse<Cart>>(`/public/${businessSlug}/cart/${sessionToken}/clear`, { method: "DELETE" });
}

export type CheckoutPayload = {
  session_token: string;
  customer: { name: string; phone: string; email?: string | null };
  shipping_address: {
    recipient_name: string;
    phone: string;
    governorate?: string | null;
    city?: string | null;
    area?: string | null;
    street_address: string;
    building?: string | null;
    floor?: string | null;
    apartment?: string | null;
    landmark?: string | null;
  };
  notes?: string | null;
  wholesale_phone?: string | null;
  wholesale_token?: string | null;
};

export async function submitCheckout(businessSlug: string, payload: CheckoutPayload) {
  return apiRequest<ApiResponse<Order>>(`/public/${businessSlug}/checkout`, {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function getPublicOrder(businessSlug: string, orderNumber: string, phone: string) {
  return apiRequest<ApiResponse<Order>>(`/public/${businessSlug}/orders/${orderNumber}?phone=${encodeURIComponent(phone)}`);
}

export async function listPublicPaymentMethods(businessSlug: string) {
  return apiRequest<ApiResponse<PaymentMethod[]>>(`/public/${businessSlug}/payment-methods`);
}

export async function getPublicOrderPaymentOptions(businessSlug: string, orderNumber: string, phone: string) {
  return apiRequest<ApiResponse<PublicOrderPaymentOptions>>(`/public/${businessSlug}/orders/${orderNumber}/payment-options?phone=${encodeURIComponent(phone)}`);
}

export type ManualPaymentProofPayload = {
  phone: string;
  payment_method_id?: number;
  method_key?: string;
  amount: string | number;
  payer_name?: string | null;
  sender_account?: string | null;
  transaction_reference?: string | null;
  proof_image?: string | null;
  notes?: string | null;
};

export async function submitManualPaymentProof(businessSlug: string, orderNumber: string, payload: ManualPaymentProofPayload) {
  return apiRequest<ApiResponse<ManualPaymentProof>>(`/public/${businessSlug}/orders/${orderNumber}/manual-payment-proofs`, {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function selectCashOnDelivery(businessSlug: string, orderNumber: string, phone: string) {
  return apiRequest<ApiResponse<Payment>>(`/public/${businessSlug}/orders/${orderNumber}/cash-on-delivery`, {
    method: "POST",
    body: JSON.stringify({ phone }),
  });
}

export async function initiatePaymobPayment(businessSlug: string, orderNumber: string, payload: { phone: string; payment_method_id?: number; method_key?: string }) {
  return apiRequest<ApiResponse<PaymobInitiationResponse>>(`/public/${businessSlug}/orders/${orderNumber}/paymob/initiate`, {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function getPublicPaymentStatus(businessSlug: string, orderNumber: string, phone: string) {
  return apiRequest<ApiResponse<PublicPaymentStatus>>(`/public/${businessSlug}/orders/${orderNumber}/payment-status?phone=${encodeURIComponent(phone)}`);
}

export type WholesaleApplicationPayload = {
  applicant_name: string;
  phone: string;
  email?: string | null;
  company_name?: string | null;
  shop_name?: string | null;
  tax_number?: string | null;
  commercial_record?: string | null;
  governorate?: string | null;
  city?: string | null;
  address?: string | null;
  message?: string | null;
};

export async function submitWholesaleApplication(businessSlug: string, payload: WholesaleApplicationPayload) {
  return apiRequest<ApiResponse<WholesaleApplication>>(`/public/${businessSlug}/wholesale/apply`, {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function getWholesaleStatus(businessSlug: string, phone: string) {
  return apiRequest<ApiResponse<{ type: "customer" | "application" | "none"; status?: string | null }>>(
    `/public/${businessSlug}/wholesale/status?phone=${encodeURIComponent(phone)}`,
  );
}

export async function requestWholesaleAccess(businessSlug: string, phone: string) {
  return apiRequest<ApiResponse<WholesaleAccess>>(`/public/${businessSlug}/wholesale/access`, {
    method: "POST",
    body: JSON.stringify({ phone }),
  });
}

export async function listPublicWholesaleProducts(businessSlug: string, access: { phone: string; token: string }, params?: URLSearchParams) {
  const query = new URLSearchParams(params);
  query.set("phone", access.phone);
  query.set("token", access.token);
  return apiRequest<PaginatedResponse<WholesalePricing>>(`/public/${businessSlug}/wholesale/products?${query.toString()}`);
}

export async function getPublicWholesaleProduct(businessSlug: string, productSlug: string, access: { phone: string; token: string }) {
  const query = new URLSearchParams({ phone: access.phone, token: access.token });
  return apiRequest<ApiResponse<WholesalePricing>>(`/public/${businessSlug}/wholesale/products/${productSlug}?${query.toString()}`);
}

export async function listWholesaleApplications(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<WholesaleApplication>>(withQuery("/wholesale/applications", params));
}

export async function getWholesaleApplication(id: string | number) {
  return apiRequest<ApiResponse<WholesaleApplication>>(`/wholesale/applications/${id}`);
}

export async function approveWholesaleApplication(id: string | number, payload: { price_list_id?: number | null; notes?: string | null }) {
  return apiRequest<ApiResponse<WholesaleApplication>>(`/wholesale/applications/${id}/approve`, { method: "POST", body: JSON.stringify(payload) });
}

export async function rejectWholesaleApplication(id: string | number, payload: { rejection_reason: string; notes?: string | null }) {
  return apiRequest<ApiResponse<WholesaleApplication>>(`/wholesale/applications/${id}/reject`, { method: "POST", body: JSON.stringify(payload) });
}

export async function listWholesaleCustomers(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<WholesaleCustomer>>(withQuery("/wholesale/customers", params));
}

export async function getWholesaleCustomer(id: string | number) {
  return apiRequest<ApiResponse<WholesaleCustomer>>(`/wholesale/customers/${id}`);
}

export async function updateWholesaleCustomer(id: string | number, payload: Partial<WholesaleCustomer>) {
  return apiRequest<ApiResponse<WholesaleCustomer>>(`/wholesale/customers/${id}`, { method: "PATCH", body: JSON.stringify(payload) });
}

export async function assignWholesaleCustomerPriceList(id: string | number, price_list_id: number, notes?: string | null) {
  return apiRequest<ApiResponse<WholesaleCustomer>>(`/wholesale/customers/${id}/assign-price-list`, { method: "POST", body: JSON.stringify({ price_list_id, notes }) });
}

export async function approveWholesaleCustomer(id: string | number) {
  return apiRequest<ApiResponse<WholesaleCustomer>>(`/wholesale/customers/${id}/approve`, { method: "POST" });
}

export async function rejectWholesaleCustomer(id: string | number, rejection_reason: string) {
  return apiRequest<ApiResponse<WholesaleCustomer>>(`/wholesale/customers/${id}/reject`, { method: "POST", body: JSON.stringify({ rejection_reason }) });
}

export async function getWholesalePricingPreview(id: string | number) {
  return apiRequest<ApiResponse<WholesalePricing[]>>(`/wholesale/customers/${id}/pricing-preview`);
}

export async function getPaymobReturnStatus(params: URLSearchParams) {
  return apiRequest<ApiResponse<{ payment_id?: number | null; status: string }>>(`/public/paymob/return?${params.toString()}`);
}

export async function listOrders(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Order>>(withQuery("/commerce/orders", params));
}

export async function getOrder(id: string | number) {
  return apiRequest<ApiResponse<Order>>(`/commerce/orders/${id}`);
}

export async function updateOrderStatus(id: string | number, status: OrderStatus, note?: string | null) {
  return apiRequest<ApiResponse<Order>>(`/commerce/orders/${id}/status`, {
    method: "PUT",
    body: JSON.stringify({ status, note }),
  });
}

export async function cancelOrder(id: string | number, note?: string | null) {
  return apiRequest<ApiResponse<Order>>(`/commerce/orders/${id}/cancel`, {
    method: "POST",
    body: JSON.stringify({ note }),
  });
}

export type BranchPayload = Partial<Branch> & { business_unit_id: number; name_ar: string; slug: string };
export type WarehousePayload = Partial<Warehouse> & { business_unit_id: number; name_ar: string; slug: string };
export type StockReceivePayload = { business_unit_id: number; warehouse_id: number; product_id: number; product_variant_id?: number | null; sku?: string | null; quantity: string | number; note?: string | null };
export type StockAdjustPayload = StockReceivePayload & { type: "adjustment_in" | "adjustment_out" };
export type StockTransferPayload = { business_unit_id: number; from_warehouse_id: number; to_warehouse_id: number; note?: string | null; items: Array<{ product_id: number; product_variant_id?: number | null; sku?: string | null; quantity: string | number }> };

export async function getInventorySummary(params?: URLSearchParams) {
  return apiRequest<ApiResponse<InventorySummary>>(withQuery("/inventory/summary", params));
}

export async function listBranches(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Branch>>(withQuery("/inventory/branches", params));
}

export async function createBranch(payload: BranchPayload) {
  return apiRequest<ApiResponse<Branch>>("/inventory/branches", { method: "POST", body: JSON.stringify(payload) });
}

export async function updateBranch(id: string | number, payload: BranchPayload) {
  return apiRequest<ApiResponse<Branch>>(`/inventory/branches/${id}`, { method: "PATCH", body: JSON.stringify(payload) });
}

export async function deleteBranch(id: string | number) {
  return apiRequest<ApiResponse<Branch>>(`/inventory/branches/${id}`, { method: "DELETE" });
}

export async function listWarehouses(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Warehouse>>(withQuery("/inventory/warehouses", params));
}

export async function createWarehouse(payload: WarehousePayload) {
  return apiRequest<ApiResponse<Warehouse>>("/inventory/warehouses", { method: "POST", body: JSON.stringify(payload) });
}

export async function updateWarehouse(id: string | number, payload: WarehousePayload) {
  return apiRequest<ApiResponse<Warehouse>>(`/inventory/warehouses/${id}`, { method: "PATCH", body: JSON.stringify(payload) });
}

export async function deleteWarehouse(id: string | number) {
  return apiRequest<ApiResponse<Warehouse>>(`/inventory/warehouses/${id}`, { method: "DELETE" });
}

export async function listStockItems(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<StockItem>>(withQuery("/inventory/stock-items", params));
}

export async function receiveStock(payload: StockReceivePayload) {
  return apiRequest<ApiResponse<StockItem>>("/inventory/stock-items/receive", { method: "POST", body: JSON.stringify(payload) });
}

export async function adjustStock(payload: StockAdjustPayload) {
  return apiRequest<ApiResponse<StockItem>>("/inventory/stock-items/adjust", { method: "POST", body: JSON.stringify(payload) });
}

export async function listStockMovements(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<StockMovement>>(withQuery("/inventory/movements", params));
}

export async function listStockTransfers(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<StockTransfer>>(withQuery("/inventory/transfers", params));
}

export async function createStockTransfer(payload: StockTransferPayload) {
  return apiRequest<ApiResponse<StockTransfer>>("/inventory/transfers", { method: "POST", body: JSON.stringify(payload) });
}

export async function approveStockTransfer(id: string | number) {
  return apiRequest<ApiResponse<StockTransfer>>(`/inventory/transfers/${id}/approve`, { method: "POST" });
}

export async function completeStockTransfer(id: string | number) {
  return apiRequest<ApiResponse<StockTransfer>>(`/inventory/transfers/${id}/complete`, { method: "POST" });
}

export async function cancelStockTransfer(id: string | number) {
  return apiRequest<ApiResponse<StockTransfer>>(`/inventory/transfers/${id}/cancel`, { method: "POST" });
}

export async function fulfillOrderStock(id: string | number) {
  return apiRequest<ApiResponse<unknown>>(`/inventory/orders/${id}/fulfill-stock`, { method: "POST" });
}

export type PaymentMethodPayload = Partial<PaymentMethod> & {
  business_unit_id: number;
  key: string;
  type: PaymentMethodType;
  name_ar: string;
};

export async function listPaymentMethods(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<PaymentMethod>>(withQuery("/payments/methods", params));
}

export async function getPaymentMethod(id: string | number) {
  return apiRequest<ApiResponse<PaymentMethod>>(`/payments/methods/${id}`);
}

export async function createPaymentMethod(payload: PaymentMethodPayload) {
  return apiRequest<ApiResponse<PaymentMethod>>("/payments/methods", { method: "POST", body: JSON.stringify(payload) });
}

export async function updatePaymentMethod(id: string | number, payload: Partial<PaymentMethodPayload>) {
  return apiRequest<ApiResponse<PaymentMethod>>(`/payments/methods/${id}`, { method: "PATCH", body: JSON.stringify(payload) });
}

export async function togglePaymentMethod(id: string | number) {
  return apiRequest<ApiResponse<PaymentMethod>>(`/payments/methods/${id}/toggle`, { method: "POST" });
}

export async function listPayments(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Payment>>(withQuery("/payments", params));
}

export async function listPaymobTransactions(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<PaymentTransaction>>(withQuery("/payments/paymob/transactions", params));
}

export async function getPayment(id: string | number) {
  return apiRequest<ApiResponse<Payment>>(`/payments/${id}`);
}

export async function listManualPaymentProofs(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<ManualPaymentProof>>(withQuery("/payments/manual-proofs", params));
}

export async function getManualPaymentProof(id: string | number) {
  return apiRequest<ApiResponse<ManualPaymentProof>>(`/payments/manual-proofs/${id}`);
}

export async function approveManualPaymentProof(id: string | number, admin_notes?: string | null) {
  return apiRequest<ApiResponse<ManualPaymentProof>>(`/payments/manual-proofs/${id}/approve`, {
    method: "POST",
    body: JSON.stringify({ admin_notes }),
  });
}

export async function rejectManualPaymentProof(id: string | number, rejected_reason: string, admin_notes?: string | null) {
  return apiRequest<ApiResponse<ManualPaymentProof>>(`/payments/manual-proofs/${id}/reject`, {
    method: "POST",
    body: JSON.stringify({ rejected_reason, admin_notes }),
  });
}

export async function markOrderPaidManually(id: string | number, payload: { amount?: string | number; reference?: string | null; notes?: string | null }) {
  return apiRequest<ApiResponse<Payment>>(`/payments/orders/${id}/mark-paid-manually`, { method: "POST", body: JSON.stringify(payload) });
}

export async function markOrderCashOnDelivery(id: string | number) {
  return apiRequest<ApiResponse<Payment>>(`/payments/orders/${id}/cash-on-delivery`, { method: "POST" });
}

export type CustomerPayload = Partial<Customer> & {
  business_unit_id: number;
  type: CustomerType;
  name: string;
  phone: string;
};

export async function listCustomers(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<Customer>>(withQuery("/commerce/customers", params));
}

export async function getCustomer(id: string | number) {
  return apiRequest<ApiResponse<Customer>>(`/commerce/customers/${id}`);
}

export async function createCustomer(payload: CustomerPayload) {
  return apiRequest<ApiResponse<Customer>>("/commerce/customers", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function updateCustomer(id: string | number, payload: Partial<CustomerPayload>) {
  return apiRequest<ApiResponse<Customer>>(`/commerce/customers/${id}`, {
    method: "PATCH",
    body: JSON.stringify(payload),
  });
}

export async function listPublicRealEstateProjects(businessSlug: string) {
  return apiRequest<ApiResponse<RealEstateProject[]>>(`/public/${businessSlug}/real-estate/projects`);
}

export async function getPublicRealEstateProject(businessSlug: string, projectSlug: string) {
  return apiRequest<ApiResponse<RealEstateProject>>(`/public/${businessSlug}/real-estate/projects/${projectSlug}`);
}

export async function listPublicPropertyUnits(businessSlug: string, params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<PropertyUnit>>(withQuery(`/public/${businessSlug}/real-estate/units`, params));
}

export async function submitRealEstateLead(businessSlug: string, payload: Partial<RealEstateLead>) {
  return apiRequest<ApiResponse<RealEstateLead>>(`/public/${businessSlug}/real-estate/leads`, { method: "POST", body: JSON.stringify(payload) });
}

export async function listRealEstateProjects(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<RealEstateProject>>(withQuery("/real-estate/projects", params));
}

export async function listRealEstateLeads(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<RealEstateLead>>(withQuery("/real-estate/leads", params));
}

export async function listPublicServices(businessSlug: string) {
  return apiRequest<ApiResponse<ImportExportService[]>>(`/public/${businessSlug}/services`);
}

export async function getPublicService(businessSlug: string, serviceSlug: string) {
  return apiRequest<ApiResponse<ImportExportService>>(`/public/${businessSlug}/services/${serviceSlug}`);
}

export async function submitRfqRequest(businessSlug: string, payload: Record<string, unknown>) {
  return apiRequest<ApiResponse<RfqRequest>>(`/public/${businessSlug}/rfq-requests`, { method: "POST", body: JSON.stringify(payload) });
}

export async function getPublicRfqStatus(businessSlug: string, rfqNumber: string, contact: string) {
  return apiRequest<ApiResponse<RfqRequest>>(`/public/${businessSlug}/rfq-requests/${rfqNumber}/status?contact=${encodeURIComponent(contact)}`);
}

export async function listRfqRequests(params?: URLSearchParams) {
  return apiRequest<PaginatedResponse<RfqRequest>>(withQuery("/services-rfq/rfq-requests", params));
}

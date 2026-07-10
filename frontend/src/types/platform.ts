export type BusinessUnit = {
  id: number;
  parent_id?: number | null;
  name_ar: string;
  name_en?: string | null;
  slug: string;
  type: string;
  status: string;
  logo?: string | null;
  cover_image?: string | null;
  description?: string | null;
  primary_color?: string | null;
  secondary_color?: string | null;
  settings_json?: Record<string, unknown>;
  enabled_modules_count?: number;
  modules?: BusinessUnitModule[];
  settings?: BusinessUnitSetting[];
};

export type ActivityTemplate = {
  id: number;
  key: string;
  name: string;
  description?: string | null;
  type: string;
  default_modules_json: string[];
  default_settings_json: Record<string, unknown>;
  is_active: boolean;
};

export type ActivityModule = {
  id: number;
  key: string;
  name: string;
  description?: string | null;
  category?: string | null;
  is_active: boolean;
};

export type BusinessUnitModule = {
  id: number;
  business_unit_id: number;
  activity_module_id: number;
  key: string;
  name: string;
  category?: string | null;
  is_enabled: boolean;
  settings_json?: Record<string, unknown>;
};

export type BusinessUnitSetting = {
  id: number;
  business_unit_id: number;
  key: string;
  value: unknown;
  type?: string | null;
  group?: string | null;
};

export type FeatureFlag = {
  id: number;
  business_unit_id?: number | null;
  key: string;
  value: boolean;
  description?: string | null;
};

export type ApiResponse<T> = {
  success: boolean;
  message: string;
  data: T;
  errors?: Record<string, string[]>;
};

export type AuthUser = {
  id: number;
  name: string;
  email: string;
  status: string;
  roles: string[];
  permissions: string[];
  business_units: Array<{
    id: number;
    name_ar: string;
    name_en?: string | null;
    slug: string;
    role?: string | null;
  }>;
};

export type LoginResponse = {
  user: AuthUser;
  token: string;
};

export type CmsPageStatus = "draft" | "published" | "archived";
export type CmsPageType = "home" | "about" | "contact" | "business_unit_landing" | "standard" | "custom";
export type CmsSectionType = "hero" | "text" | "image_text" | "cards" | "stats" | "business_units" | "branches" | "contact_cta" | "custom";
export type InquiryStatus = "new" | "in_progress" | "resolved" | "spam" | "archived";

export type CmsSection = {
  id?: number;
  cms_page_id?: number;
  section_type: CmsSectionType;
  title_ar?: string | null;
  title_en?: string | null;
  subtitle_ar?: string | null;
  subtitle_en?: string | null;
  body_ar?: string | null;
  body_en?: string | null;
  image?: string | null;
  button_label_ar?: string | null;
  button_label_en?: string | null;
  button_url?: string | null;
  data_json?: Record<string, unknown>;
  sort_order?: number;
  is_active?: boolean;
};

export type CmsPage = {
  id: number;
  business_unit_id?: number | null;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en" | "type"> | null;
  title_ar: string;
  title_en?: string | null;
  slug: string;
  page_type: CmsPageType;
  status: CmsPageStatus;
  excerpt_ar?: string | null;
  excerpt_en?: string | null;
  content_ar?: string | null;
  content_en?: string | null;
  seo_title_ar?: string | null;
  seo_title_en?: string | null;
  seo_description_ar?: string | null;
  seo_description_en?: string | null;
  featured_image?: string | null;
  sort_order: number;
  published_at?: string | null;
  sections?: CmsSection[];
};

export type CmsMenuItem = {
  id: number;
  label_ar: string;
  label_en?: string | null;
  url: string;
  sort_order: number;
  is_active: boolean;
  children?: CmsMenuItem[];
};

export type CmsMenu = {
  id: number;
  business_unit_id?: number | null;
  name: string;
  location: string;
  is_active: boolean;
  items?: CmsMenuItem[];
};

export type ContactInquiry = {
  id: number;
  business_unit_id?: number | null;
  name: string;
  email?: string | null;
  phone?: string | null;
  subject?: string | null;
  message: string;
  source_page?: string | null;
  status: InquiryStatus;
  created_at?: string;
};

export type PaginatedResponse<T> = ApiResponse<T[]> & {
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
};

export type CategoryStatus = "active" | "inactive" | "archived";
export type BrandStatus = "active" | "inactive" | "archived";
export type ProductStatus = "draft" | "published" | "archived";
export type ProductType = "simple" | "variable" | "bundle";
export type ProductVisibility = "public" | "hidden" | "private";
export type PriceListType = "retail" | "wholesale" | "distributor" | "special";

export type Category = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  parent_id?: number | null;
  name_ar: string;
  name_en?: string | null;
  slug: string;
  description_ar?: string | null;
  description_en?: string | null;
  image?: string | null;
  status: CategoryStatus;
  sort_order: number;
  seo_title_ar?: string | null;
  seo_title_en?: string | null;
  seo_description_ar?: string | null;
  seo_description_en?: string | null;
};

export type Brand = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  name_ar: string;
  name_en?: string | null;
  slug: string;
  description_ar?: string | null;
  description_en?: string | null;
  logo?: string | null;
  status: BrandStatus;
  sort_order: number;
};

export type ProductVariant = {
  id?: number;
  product_id?: number;
  name_ar?: string | null;
  name_en?: string | null;
  sku?: string | null;
  barcode?: string | null;
  option_values_json?: Record<string, unknown>;
  price_adjustment?: string | number;
  sort_order?: number;
  is_active?: boolean;
};

export type ProductImage = {
  id?: number;
  product_id?: number;
  image: string;
  alt_ar?: string | null;
  alt_en?: string | null;
  sort_order?: number;
  is_primary?: boolean;
};

export type PriceList = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  name: string;
  key: string;
  type: PriceListType;
  description?: string | null;
  is_default: boolean;
  is_active: boolean;
};

export type ProductPrice = {
  id?: number;
  business_unit_id?: number;
  product_id?: number;
  product_variant_id?: number | null;
  price_list_id: number;
  price_list?: PriceList;
  min_quantity?: number;
  price: string | number;
  compare_at_price?: string | number | null;
  starts_at?: string | null;
  ends_at?: string | null;
  is_active?: boolean;
};

export type Product = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en" | "type"> | null;
  category_id?: number | null;
  category?: Category | null;
  brand_id?: number | null;
  brand?: Brand | null;
  name_ar: string;
  name_en?: string | null;
  slug: string;
  sku?: string | null;
  product_type: ProductType;
  status?: ProductStatus;
  visibility?: ProductVisibility;
  short_description_ar?: string | null;
  short_description_en?: string | null;
  description_ar?: string | null;
  description_en?: string | null;
  featured_image?: string | null;
  base_price?: string | null;
  compare_at_price?: string | null;
  cost_price?: string | null;
  currency: string;
  is_featured: boolean;
  is_taxable?: boolean;
  min_order_quantity: number;
  max_order_quantity?: number | null;
  specs_json?: Record<string, unknown>;
  seo_title_ar?: string | null;
  seo_title_en?: string | null;
  seo_description_ar?: string | null;
  seo_description_en?: string | null;
  published_at?: string | null;
  variants?: ProductVariant[];
  images?: ProductImage[];
  prices?: ProductPrice[];
};

export type CartStatus = "active" | "converted" | "abandoned" | "expired";
export type OrderStatus = "pending_review" | "pending_payment" | "confirmed" | "processing" | "ready_to_ship" | "shipped" | "delivered" | "cancelled" | "archived";
export type PaymentStatus = "unpaid" | "pending" | "paid" | "failed" | "cancelled" | "refunded";
export type PaymentMethodType = "vodafone_cash" | "instapay" | "bank_transfer" | "cash_on_delivery" | "paymob_card" | "paymob_wallet" | "paymob_placeholder";
export type PaymentProvider = "manual" | "cod" | "paymob";
export type ManualPaymentProofStatus = "pending_review" | "approved" | "rejected" | "cancelled";
export type PaymentTransactionType = "manual_proof_submitted" | "manual_approved" | "manual_rejected" | "cod_selected" | "admin_mark_paid" | "admin_mark_failed";
export type FulfillmentStatus = "unfulfilled" | "preparing" | "ready" | "shipped" | "delivered" | "cancelled";
export type CustomerType = "individual" | "shop" | "company" | "distributor";
export type AddressType = "shipping" | "billing";

export type CustomerAddress = {
  id?: number;
  customer_id?: number;
  type: AddressType;
  label?: string | null;
  recipient_name: string;
  phone: string;
  country?: string;
  governorate?: string | null;
  city?: string | null;
  area?: string | null;
  street_address: string;
  building?: string | null;
  floor?: string | null;
  apartment?: string | null;
  landmark?: string | null;
  postal_code?: string | null;
  is_default?: boolean;
};

export type Customer = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  type: CustomerType;
  name: string;
  email?: string | null;
  phone: string;
  company_name?: string | null;
  tax_number?: string | null;
  commercial_record?: string | null;
  approval_status?: string | null;
  price_list_id?: number | null;
  notes?: string | null;
  addresses?: CustomerAddress[];
};

export type CartItem = {
  id: number;
  cart_id: number;
  product_id: number;
  product_variant_id?: number | null;
  sku?: string | null;
  product_name_ar: string;
  product_name_en?: string | null;
  variant_name_ar?: string | null;
  variant_name_en?: string | null;
  quantity: number;
  unit_price: string;
  subtotal: string;
};

export type Cart = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  session_token: string;
  status: CartStatus;
  currency: string;
  subtotal: string;
  discount_total: string;
  tax_total: string;
  shipping_total: string;
  grand_total: string;
  items?: CartItem[];
};

export type Order = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  customer?: Customer;
  order_number: string;
  status: OrderStatus;
  payment_status: PaymentStatus;
  fulfillment_status: FulfillmentStatus;
  currency: string;
  subtotal: string;
  discount_total: string;
  tax_total: string;
  shipping_total: string;
  grand_total: string;
  customer_name: string;
  customer_email?: string | null;
  customer_phone: string;
  shipping_address_json?: Record<string, unknown> | null;
  billing_address_json?: Record<string, unknown> | null;
  notes?: string | null;
  internal_notes?: string | null;
  placed_at?: string | null;
  items?: OrderItem[];
  status_histories?: OrderStatusHistory[];
};

export type OrderItem = {
  id: number;
  order_id: number;
  product_id?: number | null;
  product_variant_id?: number | null;
  sku?: string | null;
  product_name_ar: string;
  product_name_en?: string | null;
  variant_name_ar?: string | null;
  variant_name_en?: string | null;
  quantity: number;
  unit_price: string;
  subtotal: string;
};

export type OrderStatusHistory = {
  id: number;
  order_id: number;
  from_status?: string | null;
  to_status: string;
  note?: string | null;
  changed_by?: number | null;
  created_at?: string;
};

export type PaymentMethod = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  key: string;
  type: PaymentMethodType;
  name_ar: string;
  name_en?: string | null;
  description_ar?: string | null;
  description_en?: string | null;
  instructions_ar?: string | null;
  instructions_en?: string | null;
  destination_account?: string | null;
  destination_account_name?: string | null;
  config_json?: Record<string, unknown>;
  is_active: boolean;
  sort_order: number;
};

export type PaymentTransaction = {
  id: number;
  payment_id: number;
  type: PaymentTransactionType;
  status: PaymentStatus;
  amount: string;
  currency: string;
  reference?: string | null;
  provider?: PaymentProvider | null;
  provider_transaction_id?: string | null;
  provider_order_id?: string | null;
  provider_status?: string | null;
  processed_at?: string | null;
  verified_at?: string | null;
};

export type Payment = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  order?: Pick<Order, "id" | "order_number" | "status" | "payment_status" | "grand_total" | "currency"> | null;
  customer?: Pick<Customer, "id" | "name" | "phone" | "email"> | null;
  payment_method?: PaymentMethod | null;
  provider?: PaymentProvider | null;
  method_type: PaymentMethodType;
  method_key?: string | null;
  status: PaymentStatus;
  amount: string;
  currency: string;
  paid_at?: string | null;
  failed_at?: string | null;
  reference?: string | null;
  provider_reference?: string | null;
  provider_status?: string | null;
  checkout_url?: string | null;
  notes?: string | null;
  transactions?: PaymentTransaction[];
};

export type ManualPaymentProof = {
  id: number;
  business_unit_id: number;
  business_unit?: Pick<BusinessUnit, "id" | "slug" | "name_ar" | "name_en"> | null;
  order?: Pick<Order, "id" | "order_number" | "status" | "payment_status" | "customer_name" | "customer_phone" | "grand_total" | "currency"> | null;
  payment_method?: PaymentMethod | null;
  payment?: Payment | null;
  status: ManualPaymentProofStatus;
  amount: string;
  payer_name?: string | null;
  sender_account?: string | null;
  transaction_reference?: string | null;
  proof_image?: string | null;
  notes?: string | null;
  admin_notes?: string | null;
  reviewer?: { id: number; name: string } | null;
  reviewed_at?: string | null;
  rejected_reason?: string | null;
  created_at?: string;
};

export type PublicOrderPaymentOptions = {
  order: Pick<Order, "id" | "order_number" | "status" | "payment_status" | "grand_total" | "currency" | "customer_name" | "customer_phone">;
  payment_methods: PaymentMethod[];
  proofs: ManualPaymentProof[];
};

export type PaymobInitiationResponse = {
  payment_id: number;
  payment_status: PaymentStatus;
  checkout_url?: string | null;
  iframe_url?: string | null;
  provider_reference?: string | null;
  message: string;
};

export type PublicPaymentStatus = {
  order: Pick<Order, "order_number" | "status" | "payment_status" | "grand_total" | "currency">;
  payment?: Pick<Payment, "id" | "provider" | "method_type" | "status" | "provider_status" | "provider_reference"> | null;
};

export type Lead = {
  id: number;
  businessUnitId: number;
  name: string;
  email?: string;
  phone?: string;
  status: string;
};

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

export type Product = {
  id: number;
  businessUnitId?: number;
  name: string;
  slug: string;
  sku?: string;
  status: string;
};

export type Order = {
  id: number;
  businessUnitId: number;
  number: string;
  status: string;
  total: number;
  currency: string;
};

export type Payment = {
  id: number;
  businessUnitId: number;
  provider: string;
  status: string;
  amount: number;
  currency: string;
};

export type Lead = {
  id: number;
  businessUnitId: number;
  name: string;
  email?: string;
  phone?: string;
  status: string;
};

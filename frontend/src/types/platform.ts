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

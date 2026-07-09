import type {
  ActivityModule,
  ActivityTemplate,
  ApiResponse,
  BusinessUnit,
  BusinessUnitModule,
  BusinessUnitSetting,
  CmsMenu,
  CmsPage,
  CmsPageStatus,
  CmsPageType,
  CmsSection,
  ContactInquiry,
  InquiryStatus,
  FeatureFlag,
  LoginResponse,
  PaginatedResponse,
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

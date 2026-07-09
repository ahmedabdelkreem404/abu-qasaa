import { getBusinessUnitPublicCmsPage, getPublicBusinessUnitBySlug } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState } from "@/components/shared/api-state";
import type { BusinessUnit, CmsPage } from "@/types/platform";

const typeMessages: Record<string, string> = {
  product_store: "Product store coming soon",
  wholesale_store: "Wholesale store coming soon",
  services_rfq: "Services and RFQ coming soon",
  real_estate: "Real estate coming soon",
  content_only: "Content page coming soon",
  hybrid: "Hybrid business unit coming soon",
};

async function loadBusinessUnit(slug: string): Promise<BusinessUnit | null> {
  try {
    const response = await getPublicBusinessUnitBySlug(slug);
    return response.data;
  } catch {
    return null;
  }
}

export default async function BusinessHomePage({
  params,
}: {
  params: Promise<{ businessSlug: string }>;
}) {
  const { businessSlug } = await params;
  const unit = await loadBusinessUnit(businessSlug);

  if (unit === null) {
    return <ApiErrorState message="This business unit is not available yet." />;
  }

  const page: CmsPage | null = await getBusinessUnitPublicCmsPage(businessSlug)
    .then((response) => response.data)
    .catch(() => null);

  if (page) {
    return (
      <section className="space-y-6">
        <h1 className="text-3xl font-semibold">{page.title_en ?? page.title_ar}</h1>
        <SectionRenderer sections={page.sections} />
      </section>
    );
  }

  return (
    <section className="space-y-6">
      <div className="space-y-2">
        <p className="text-sm font-medium uppercase tracking-wide text-teal-700">
          {unit.type}
        </p>
        <h1 className="text-3xl font-semibold">{unit.name_en ?? unit.name_ar}</h1>
        <p className="max-w-2xl text-slate-600">
          {unit.description ?? typeMessages[unit.type] ?? "Business unit coming soon"}
        </p>
      </div>
      <div className="rounded-md border border-slate-200 bg-white p-6">
        {typeMessages[unit.type] ?? "Business unit coming soon"}
      </div>
    </section>
  );
}

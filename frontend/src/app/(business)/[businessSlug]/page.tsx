import { getBusinessUnitPublicCmsPage, getPublicBusinessUnitBySlug, listPublicCollections, listPublicProducts } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState } from "@/components/shared/api-state";
import { getLocale } from "@/i18n/server";
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
  const locale = await getLocale();
  const unit = await loadBusinessUnit(businessSlug);

  if (unit === null) {
    return <ApiErrorState message="This business unit is not available yet." />;
  }

  const page: CmsPage | null = await getBusinessUnitPublicCmsPage(businessSlug)
    .then((response) => response.data)
    .catch(() => null);
  const hasProducts = unit.modules?.some((module) => module.key === "products" && module.is_enabled) ?? false;
  const hasWholesale = (unit.modules?.some((module) => module.key === "wholesale" && module.is_enabled) ?? false)
    && (unit.settings?.some((setting) => setting.key === "wholesale_enabled" && Boolean(setting.value)) ?? unit.type === "wholesale_store");
  const featuredProducts = hasProducts
    ? await listPublicProducts(businessSlug, new URLSearchParams({ is_featured: "true", per_page: "3" })).then((response) => response.data).catch(() => [])
    : [];
  const collections = hasProducts ? await listPublicCollections(businessSlug).then((response) => response.data.slice(0, 3)).catch(() => []) : [];

  if (page) {
    return (
      <section className="space-y-6">
        <div className="aq-card p-6">
          <p className="aq-eyebrow">{unit.type}</p>
          <h1 className="aq-title">{locale === "ar" ? page.title_ar : (page.title_en ?? page.title_ar)}</h1>
          {unit.description ? <p className="aq-subtitle mt-2">{unit.description}</p> : null}
        </div>
        <SectionRenderer sections={page.sections} />
        {hasProducts ? <MerchLinks businessSlug={businessSlug} /> : null}
        {featuredProducts.length > 0 ? (
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="text-2xl font-black">Featured products</h2>
              <a href={`/${businessSlug}/products`} className="text-sm font-black text-[var(--aq-primary)]">View products</a>
            </div>
            <div className="aq-grid-auto">
              {featuredProducts.map((product) => (
                <a key={product.id} href={`/${businessSlug}/products/${product.slug}`} className="aq-card p-5">
                  <h3 className="font-black">{product.name_en ?? product.name_ar}</h3>
                  <p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{product.short_description_en ?? product.short_description_ar ?? product.category?.name_en}</p>
                </a>
              ))}
            </div>
          </div>
        ) : null}
      </section>
    );
  }

  return (
    <section className="space-y-6">
      <div className="space-y-2">
        <p className="aq-eyebrow">
          {unit.type}
        </p>
        <h1 className="aq-title">{locale === "ar" ? unit.name_ar : (unit.name_en ?? unit.name_ar)}</h1>
        <p className="aq-subtitle max-w-2xl">
          {unit.description ?? typeMessages[unit.type] ?? "Business unit coming soon"}
        </p>
        {hasWholesale ? <a href={`/${businessSlug}/wholesale`} className="aq-btn mt-4">Wholesale / Become a partner</a> : null}
      </div>
      {hasProducts ? <MerchLinks businessSlug={businessSlug} /> : <div className="aq-card p-6">{typeMessages[unit.type] ?? "Business unit coming soon"}</div>}
      {collections.length > 0 ? <div className="aq-grid-auto">{collections.map((collection) => <a key={collection.id} href={`/${businessSlug}/collections/${collection.slug}`} className="aq-card p-5"><h2 className="font-black">{collection.name_en ?? collection.name_ar}</h2><p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{collection.description_en ?? collection.description_ar ?? "Curated selection."}</p></a>)}</div> : null}
    </section>
  );
}

function MerchLinks({ businessSlug }: { businessSlug: string }) {
  return (
    <div className="flex flex-wrap gap-2">
      {[
        ["Collections", `/${businessSlug}/collections`],
        ["Gift boxes", `/${businessSlug}/gift-boxes`],
        ["Corporate gifts", `/${businessSlug}/corporate-gifts`],
        ["Seasonal", `/${businessSlug}/seasonal`],
      ].map(([label, href]) => <a key={href} href={href} className="aq-btn-secondary">{label}</a>)}
    </div>
  );
}

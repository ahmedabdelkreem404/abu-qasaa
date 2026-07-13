import { listPublicBrands, listPublicCategories, listPublicProducts } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import { getDictionary, getLocale } from "@/i18n/server";
import { StorefrontProductGrid } from "@/storefront/components";
import { getStorefrontProfile, localized } from "@/storefront/profiles";

export default async function BusinessProductsPage({
  params,
}: {
  params: Promise<{ businessSlug: string }>;
}) {
  const { businessSlug } = await params;
  const [products, categories, brands, dictionary, locale] = await Promise.all([
    listPublicProducts(businessSlug).then((response) => response.data).catch(() => null),
    listPublicCategories(businessSlug).then((response) => response.data).catch(() => []),
    listPublicBrands(businessSlug).then((response) => response.data).catch(() => []),
    getDictionary(),
    getLocale(),
  ]);
  const profile = getStorefrontProfile(businessSlug);

  if (products === null) {
    return <ApiErrorState message="Products are not available for this business unit." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-store-kicker">{businessSlug}</p>
        <h1 className="aq-store-title">{dictionary.public.productsTitle}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{dictionary.public.productsBody}</p>
      </div>
      <div className="aq-card p-4">
        <div className="grid gap-3 md:grid-cols-4">
          <input placeholder={dictionary.common.search} className="px-3 py-2.5 text-sm" />
          <select className="px-3 py-2.5 text-sm"><option>{dictionary.common.category}</option>{categories.map((item) => <option key={item.id}>{localized(locale, item.name_ar, item.name_en)}</option>)}</select>
          <select className="px-3 py-2.5 text-sm"><option>{dictionary.common.brandLabel}</option>{brands.map((item) => <option key={item.id}>{localized(locale, item.name_ar, item.name_en)}</option>)}</select>
          <input placeholder={dictionary.common.priceRange} className="px-3 py-2.5 text-sm" />
        </div>
      </div>
      {products.length === 0 ? <EmptyState message="No published products yet." /> : (
        <StorefrontProductGrid businessSlug={businessSlug} products={products} empty={dictionary.common.noData} locale={locale} profile={profile} />
      )}
    </section>
  );
}

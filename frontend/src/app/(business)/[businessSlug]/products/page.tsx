import { listPublicBrands, listPublicCategories, listPublicProducts } from "@/api/client";
import { AddToCartButton } from "@/commerce/cart-tools";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import { getDictionary } from "@/i18n/server";
import Link from "next/link";

export default async function BusinessProductsPage({
  params,
}: {
  params: Promise<{ businessSlug: string }>;
}) {
  const { businessSlug } = await params;
  const [products, categories, brands, dictionary] = await Promise.all([
    listPublicProducts(businessSlug).then((response) => response.data).catch(() => null),
    listPublicCategories(businessSlug).then((response) => response.data).catch(() => []),
    listPublicBrands(businessSlug).then((response) => response.data).catch(() => []),
    getDictionary(),
  ]);

  if (products === null) {
    return <ApiErrorState message="Products are not available for this business unit." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-eyebrow">{businessSlug}</p>
        <h1 className="aq-title">{dictionary.public.productsTitle}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{dictionary.public.productsBody}</p>
      </div>
      <div className="aq-card p-4">
        <div className="grid gap-3 md:grid-cols-4">
          <input placeholder={dictionary.common.search} className="px-3 py-2.5 text-sm" />
          <select className="px-3 py-2.5 text-sm"><option>{dictionary.common.category}</option>{categories.map((item) => <option key={item.id}>{item.name_en ?? item.name_ar}</option>)}</select>
          <select className="px-3 py-2.5 text-sm"><option>{dictionary.common.brandLabel}</option>{brands.map((item) => <option key={item.id}>{item.name_en ?? item.name_ar}</option>)}</select>
          <input placeholder={dictionary.common.priceRange} className="px-3 py-2.5 text-sm" />
        </div>
      </div>
      {products.length === 0 ? <EmptyState message="No published products yet." /> : (
        <div className="aq-grid-auto">
          {products.map((product) => (
            <div key={product.id} className="aq-card p-5 transition hover:-translate-y-1 hover:border-[color:var(--aq-primary)]">
              <Link href={`/${businessSlug}/products/${product.slug}`}>
                <div className="flex aspect-[4/3] items-center justify-center rounded-md bg-[var(--aq-soft)] text-sm font-bold text-[var(--aq-muted)]">{dictionary.common.productImage}</div>
                <div className="mt-4 flex flex-wrap gap-2">
                  {product.badges?.slice(0, 2).map((badge) => <span key={badge.id} className="aq-chip">{badge.name_en ?? badge.name_ar}</span>)}
                  {product.bundle ? <span className="rounded-full bg-amber-50 px-2 py-1 text-xs font-bold text-amber-800">{product.bundle.name_en ?? product.bundle.name_ar}</span> : null}
                </div>
                <h2 className="mt-4 text-lg font-black">{product.name_en ?? product.name_ar}</h2>
                <p className="mt-2 line-clamp-2 text-sm leading-7 text-[var(--aq-muted)]">{product.short_description_en ?? product.short_description_ar ?? product.category?.name_en ?? product.category?.name_ar}</p>
                {product.base_price ? <p className="mt-3 text-lg font-black text-[var(--aq-primary)]">{product.base_price} {product.currency}</p> : null}
              </Link>
              <AddToCartButton businessSlug={businessSlug} product={product} />
            </div>
          ))}
        </div>
      )}
    </section>
  );
}

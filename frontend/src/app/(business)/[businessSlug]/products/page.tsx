import { listPublicBrands, listPublicCategories, listPublicProducts } from "@/api/client";
import { AddToCartButton } from "@/commerce/cart-tools";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import Link from "next/link";

export default async function BusinessProductsPage({
  params,
}: {
  params: Promise<{ businessSlug: string }>;
}) {
  const { businessSlug } = await params;
  const [products, categories, brands] = await Promise.all([
    listPublicProducts(businessSlug).then((response) => response.data).catch(() => null),
    listPublicCategories(businessSlug).then((response) => response.data).catch(() => []),
    listPublicBrands(businessSlug).then((response) => response.data).catch(() => []),
  ]);

  if (products === null) {
    return <ApiErrorState message="Products are not available for this business unit." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Products</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Browse published catalog products. Checkout is not available in this phase.</p>
      </div>
      <div className="rounded-md border border-slate-200 bg-white p-4">
        <div className="grid gap-3 md:grid-cols-4">
          <input placeholder="Search" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <select className="rounded-md border border-slate-300 px-3 py-2 text-sm"><option>Category</option>{categories.map((item) => <option key={item.id}>{item.name_en ?? item.name_ar}</option>)}</select>
          <select className="rounded-md border border-slate-300 px-3 py-2 text-sm"><option>Brand</option>{brands.map((item) => <option key={item.id}>{item.name_en ?? item.name_ar}</option>)}</select>
          <input placeholder="Price range" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>
      {products.length === 0 ? <EmptyState message="No published products yet." /> : (
        <div className="grid gap-4 md:grid-cols-3">
          {products.map((product) => (
            <div key={product.id} className="rounded-md border border-slate-200 bg-white p-5">
              <Link href={`/${businessSlug}/products/${product.slug}`}>
                <div className="flex aspect-[4/3] items-center justify-center rounded-md bg-slate-100 text-sm text-slate-500">Product image</div>
                <h2 className="mt-4 font-semibold">{product.name_en ?? product.name_ar}</h2>
                <p className="mt-2 line-clamp-2 text-sm text-slate-600">{product.short_description_en ?? product.short_description_ar ?? product.category?.name_en ?? product.category?.name_ar}</p>
                {product.base_price ? <p className="mt-3 text-sm font-medium text-teal-700">{product.base_price} {product.currency}</p> : null}
              </Link>
              <AddToCartButton businessSlug={businessSlug} product={product} />
            </div>
          ))}
        </div>
      )}
    </section>
  );
}

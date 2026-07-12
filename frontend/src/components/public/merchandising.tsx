import { AddToCartButton } from "@/commerce/cart-tools";
import type { Product, ProductCollection } from "@/types/platform";
import Link from "next/link";

export function ProductGrid({ businessSlug, products, empty }: { businessSlug: string; products: Product[]; empty: string }) {
  if (products.length === 0) {
    return <p className="rounded-md border border-slate-200 bg-white p-6 text-sm text-slate-600">{empty}</p>;
  }

  return (
    <div className="grid gap-4 md:grid-cols-3">
      {products.map((product) => (
        <article key={product.id} className="rounded-md border border-slate-200 bg-white p-5">
          <Link href={`/${businessSlug}/products/${product.slug}`}>
            <div className="flex aspect-[4/3] items-center justify-center rounded-md bg-slate-100 text-sm text-slate-500">Product image</div>
            <div className="mt-4 flex flex-wrap gap-2">
              {product.badges?.slice(0, 3).map((badge) => <span key={badge.id} className="rounded-sm bg-teal-50 px-2 py-1 text-xs font-medium text-teal-800">{badge.name_en ?? badge.name_ar}</span>)}
              {product.bundle ? <span className="rounded-sm bg-amber-50 px-2 py-1 text-xs font-medium text-amber-800">{product.bundle.name_en ?? product.bundle.name_ar}</span> : null}
            </div>
            <h2 className="mt-3 font-semibold">{product.name_en ?? product.name_ar}</h2>
            <p className="mt-2 line-clamp-2 text-sm text-slate-600">{product.short_description_en ?? product.short_description_ar ?? product.category?.name_en ?? product.category?.name_ar}</p>
            {product.base_price ? <p className="mt-3 text-sm font-medium text-teal-700">{product.base_price} {product.currency}</p> : null}
          </Link>
          <AddToCartButton businessSlug={businessSlug} product={product} />
        </article>
      ))}
    </div>
  );
}

export function CollectionGrid({ businessSlug, collections }: { businessSlug: string; collections: ProductCollection[] }) {
  if (collections.length === 0) {
    return <p className="rounded-md border border-slate-200 bg-white p-6 text-sm text-slate-600">No collections are available.</p>;
  }

  return (
    <div className="grid gap-4 md:grid-cols-3">
      {collections.map((collection) => (
        <Link key={collection.id} href={`/${businessSlug}/collections/${collection.slug}`} className="rounded-md border border-slate-200 bg-white p-5">
          <div className="flex aspect-[4/3] items-center justify-center rounded-md bg-slate-100 text-sm text-slate-500">Collection image</div>
          <h2 className="mt-4 font-semibold">{collection.name_en ?? collection.name_ar}</h2>
          <p className="mt-2 line-clamp-2 text-sm text-slate-600">{collection.description_en ?? collection.description_ar ?? "Curated Ghosoun selection."}</p>
        </Link>
      ))}
    </div>
  );
}

import { getPublicProductAvailability, getPublicProductBySlug } from "@/api/client";
import { AddToCartButton } from "@/commerce/cart-tools";
import { ApiErrorState } from "@/components/shared/api-state";

export default async function ProductDetailPage({
  params,
}: {
  params: Promise<{ businessSlug: string; productSlug: string }>;
}) {
  const { businessSlug, productSlug } = await params;
  const [product, availability] = await Promise.all([
    getPublicProductBySlug(businessSlug, productSlug).then((response) => response.data).catch(() => null),
    getPublicProductAvailability(businessSlug, productSlug).then((response) => response.data).catch(() => null),
  ]);

  if (!product) {
    return <ApiErrorState message="Product is not available." />;
  }

  return (
    <section className="grid gap-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
      <div className="aq-card flex aspect-square items-center justify-center bg-[var(--aq-soft)] text-sm font-bold text-[var(--aq-muted)]">Product image</div>
      <div className="space-y-6">
        <div>
          <p className="aq-eyebrow">{product.category?.name_en ?? product.category?.name_ar ?? "Product"}</p>
          <div className="mt-3 flex flex-wrap gap-2">
            {product.badges?.map((badge) => <span key={badge.id} className="aq-chip">{badge.name_en ?? badge.name_ar}</span>)}
            {product.bundle ? <span className="rounded-full bg-amber-50 px-2 py-1 text-xs font-bold text-amber-800">{product.bundle.name_en ?? product.bundle.name_ar}</span> : null}
          </div>
          <h1 className="aq-title mt-2">{product.name_en ?? product.name_ar}</h1>
          <p className="aq-subtitle mt-3">{product.short_description_en ?? product.short_description_ar}</p>
          {product.base_price ? <p className="mt-4 text-2xl font-black text-[var(--aq-primary)]">{product.base_price} {product.currency}</p> : null}
          {availability ? <p className={`mt-3 text-sm font-medium ${availability.in_stock ? "text-emerald-700" : "text-amber-700"}`}>{availability.in_stock ? "In stock" : "Out of stock"}{availability.available_quantity !== null && availability.available_quantity !== undefined ? ` · ${availability.available_quantity} available` : ""}</p> : null}
        </div>
        <div className="aq-card p-4">
          <label className="grid max-w-32 gap-1 text-sm font-bold">Quantity<input type="number" min={1} defaultValue={1} className="px-3 py-2 font-normal" /></label>
          <AddToCartButton businessSlug={businessSlug} product={product} disabled={availability?.inventory_enabled === true && !availability.in_stock} />
          <p className="mt-2 text-sm text-slate-500">No online payment yet. Orders are submitted for confirmation.</p>
        </div>
        <Info title="Description" value={product.description_en ?? product.description_ar ?? "No description yet."} />
        <Info title="Brand" value={product.brand?.name_en ?? product.brand?.name_ar ?? "-"} />
        {product.bundle ? <Info title="Bundle" value={`${product.bundle.name_en ?? product.bundle.name_ar} · ${product.bundle.bundle_type.replaceAll("_", " ")}`} /> : null}
        <div className="aq-card p-5">
          <h2 className="font-semibold">Specs</h2>
          <dl className="mt-3 grid gap-2 text-sm">
            {Object.entries(product.specs_json ?? {}).map(([key, value]) => <div key={key} className="flex justify-between gap-4"><dt className="text-slate-500">{key}</dt><dd>{String(value)}</dd></div>)}
          </dl>
        </div>
        <div className="aq-card p-5">
          <h2 className="font-semibold">Variants</h2>
          {(product.variants ?? []).length === 0 ? <p className="mt-2 text-sm text-slate-600">No variants.</p> : <ul className="mt-3 grid gap-2 text-sm">{product.variants?.map((variant) => <li key={variant.id}>{variant.name_en ?? variant.name_ar ?? variant.sku}</li>)}</ul>}
        </div>
      </div>
    </section>
  );
}

function Info({ title, value }: { title: string; value: string }) {
  return <div className="aq-card p-5"><h2 className="font-black">{title}</h2><p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{value}</p></div>;
}

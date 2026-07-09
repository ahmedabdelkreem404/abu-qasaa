"use client";

import { getProduct, publishProduct } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { Product } from "@/types/platform";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { useEffect, useState } from "react";

export default function ProductDetailsPage() {
  const { id } = useParams<{ id: string }>();
  const router = useRouter();
  const [product, setProduct] = useState<Product | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { getProduct(id).then((r) => setProduct(r.data)).catch(() => setError("Could not load product.")); }, [id]);
  async function onPublish() {
    const response = await publishProduct(id);
    setProduct(response.data);
    router.refresh();
  }
  if (error) return <ApiErrorState message={error} />;
  if (!product) return <div className="text-sm text-slate-600">Loading product...</div>;
  return <section className="space-y-6">
    <div className="flex flex-wrap items-center justify-between gap-3"><div><h1 className="text-2xl font-semibold">{product.name_en ?? product.name_ar}</h1><p className="text-sm text-slate-600">{product.slug} · {product.status} · {product.visibility}</p></div><div className="flex flex-wrap gap-2"><Link href={`/dashboard/catalog/products/${product.id}/edit`} className="rounded-md border border-slate-300 px-3 py-2 text-sm">Edit</Link><Link href={`/dashboard/catalog/products/${product.id}/variants`} className="rounded-md border border-slate-300 px-3 py-2 text-sm">Variants</Link><Link href={`/dashboard/catalog/products/${product.id}/prices`} className="rounded-md border border-slate-300 px-3 py-2 text-sm">Prices</Link><button onClick={onPublish} className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white">Publish</button></div></div>
    <div className="grid gap-4 md:grid-cols-4">
      <Metric label="Business Unit" value={product.business_unit?.slug ?? String(product.business_unit_id)} />
      <Metric label="Category" value={product.category?.name_en ?? product.category?.name_ar ?? "-"} />
      <Metric label="Brand" value={product.brand?.name_en ?? product.brand?.name_ar ?? "-"} />
      <Metric label="Base Price" value={product.base_price ?? "-"} />
    </div>
    <div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Description</h2><p className="mt-2 text-sm text-slate-600">{product.description_en ?? product.short_description_en ?? "No description."}</p></div>
    <div className="grid gap-4 md:grid-cols-2">
      <Panel title="Variants" lines={(product.variants ?? []).map((item) => `${item.name_en ?? item.name_ar ?? item.sku ?? "Variant"} ${item.is_active === false ? "(inactive)" : ""}`)} />
      <Panel title="Prices" lines={(product.prices ?? []).map((item) => `${item.price_list?.name ?? item.price_list_id}: ${item.price} from ${item.min_quantity ?? 1}`)} />
    </div>
  </section>;
}

function Metric({ label, value }: { label: string; value: string }) {
  return <div className="rounded-md border border-slate-200 bg-white p-4"><h2 className="text-sm font-medium text-slate-500">{label}</h2><p className="mt-2 font-medium">{value}</p></div>;
}

function Panel({ title, lines }: { title: string; lines: string[] }) {
  return <div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">{title}</h2>{lines.length === 0 ? <p className="mt-2 text-sm text-slate-600">None yet.</p> : <ul className="mt-3 grid gap-2 text-sm text-slate-700">{lines.map((line, index) => <li key={index}>{line}</li>)}</ul>}</div>;
}

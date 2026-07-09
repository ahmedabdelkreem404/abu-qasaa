"use client";

import { listProducts } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { Product } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function ProductsPage() {
  const [items, setItems] = useState<Product[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { listProducts().then((r) => setItems(r.data)).catch((e) => setError(e instanceof Error && e.name === "403" ? "Forbidden." : "Could not load products.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!items) return <div className="text-sm text-slate-600">Loading products...</div>;
  return <section className="space-y-6">
    <div className="flex items-center justify-between"><h1 className="text-2xl font-semibold">Products</h1><Link href="/dashboard/catalog/products/create" className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Create</Link></div>
    <div className="rounded-md border border-slate-200 bg-white p-4">
      <div className="grid gap-3 md:grid-cols-4">
        <input placeholder="Business unit ID" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
        <input placeholder="Status" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
        <input placeholder="Category ID" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
        <input placeholder="Brand ID" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
      </div>
    </div>
    {items.length === 0 ? <EmptyState message="No products yet." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Name</th><th>Business Unit</th><th>Status</th><th>Price</th></tr></thead><tbody>{items.map((item) => <tr key={item.id} className="border-t border-slate-100"><td className="p-3"><Link href={`/dashboard/catalog/products/${item.id}`} className="font-medium text-teal-700">{item.name_en ?? item.name_ar}</Link></td><td>{item.business_unit?.slug ?? item.business_unit_id}</td><td>{item.status}</td><td>{item.base_price ?? "-"}</td></tr>)}</tbody></table></div>}
  </section>;
}

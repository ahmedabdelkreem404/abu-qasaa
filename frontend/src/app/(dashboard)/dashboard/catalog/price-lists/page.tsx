"use client";

import { listPriceLists } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { PriceList } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function PriceListsPage() {
  const [items, setItems] = useState<PriceList[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { listPriceLists().then((r) => setItems(r.data)).catch((e) => setError(e instanceof Error && e.name === "403" ? "Forbidden." : "Could not load price lists.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!items) return <div className="text-sm text-slate-600">Loading price lists...</div>;
  return <section className="space-y-6">
    <div className="flex items-center justify-between"><h1 className="text-2xl font-semibold">Price Lists</h1><Link href="/dashboard/catalog/price-lists/create" className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Create</Link></div>
    {items.length === 0 ? <EmptyState message="No price lists yet." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Name</th><th>Type</th><th>Business Unit</th><th>Active</th></tr></thead><tbody>{items.map((item) => <tr key={item.id} className="border-t border-slate-100"><td className="p-3"><Link href={`/dashboard/catalog/price-lists/${item.id}/edit`} className="font-medium text-teal-700">{item.name}</Link></td><td>{item.type}</td><td>{item.business_unit?.slug ?? item.business_unit_id}</td><td>{item.is_active ? "Yes" : "No"}</td></tr>)}</tbody></table></div>}
  </section>;
}

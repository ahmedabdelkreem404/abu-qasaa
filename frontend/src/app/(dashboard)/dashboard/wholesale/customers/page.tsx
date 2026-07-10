"use client";

import { listWholesaleCustomers } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { WholesaleCustomer } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function WholesaleCustomersPage() {
  const [items, setItems] = useState<WholesaleCustomer[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => { listWholesaleCustomers().then((r) => setItems(r.data)).catch(() => setError("Could not load wholesale customers.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!items) return <p className="text-sm text-slate-600">Loading customers...</p>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Wholesale customers</h1>
      <div className="rounded-md border border-slate-200 bg-white p-4"><div className="grid gap-3 md:grid-cols-4"><input placeholder="Business unit" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input placeholder="Status" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input placeholder="Price list" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /></div></div>
      {items.length === 0 ? <EmptyState message="No wholesale customers yet." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Name</th><th>Phone</th><th>Status</th><th>Price List</th></tr></thead><tbody>{items.map((item) => <tr key={item.id} className="border-t border-slate-100"><td className="p-3"><Link href={`/dashboard/wholesale/customers/${item.id}`} className="font-medium text-teal-700">{item.name}</Link></td><td>{item.phone}</td><td>{item.wholesale_status}</td><td>{item.price_list?.name ?? item.price_list_id ?? "-"}</td></tr>)}</tbody></table></div>}
    </section>
  );
}

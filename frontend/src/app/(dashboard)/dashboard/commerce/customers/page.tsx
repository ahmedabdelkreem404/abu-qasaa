"use client";

import { listCustomers } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { Customer } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function CustomersPage() {
  const [customers, setCustomers] = useState<Customer[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { listCustomers().then((r) => setCustomers(r.data)).catch((e) => setError(e instanceof Error && e.name === "403" ? "Forbidden." : "Could not load customers.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!customers) return <div className="text-sm text-slate-600">Loading customers...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Customers</h1><div className="rounded-md border border-slate-200 bg-white p-4"><div className="grid gap-3 md:grid-cols-2"><input placeholder="Business unit" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input placeholder="Phone or search" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /></div></div>{customers.length === 0 ? <EmptyState message="No customers yet." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Name</th><th>Phone</th><th>Business Unit</th><th>Type</th></tr></thead><tbody>{customers.map((customer) => <tr key={customer.id} className="border-t border-slate-100"><td className="p-3"><Link className="font-medium text-teal-700" href={`/dashboard/commerce/customers/${customer.id}`}>{customer.name}</Link></td><td>{customer.phone}</td><td>{customer.business_unit?.slug ?? customer.business_unit_id}</td><td>{customer.type}</td></tr>)}</tbody></table></div>}</section>;
}

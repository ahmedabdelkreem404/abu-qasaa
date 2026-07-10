"use client";

import { listPaymentMethods, togglePaymentMethod } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { PaymentMethod } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function PaymentMethodsPage() {
  const [methods, setMethods] = useState<PaymentMethod[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  async function load() { const response = await listPaymentMethods(); setMethods(response.data); }
  useEffect(() => {
    let active = true;
    listPaymentMethods()
      .then((response) => { if (active) setMethods(response.data); })
      .catch(() => { if (active) setError("Could not load payment methods."); });
    return () => { active = false; };
  }, []);
  async function onToggle(id: number) { await togglePaymentMethod(id); await load(); }
  if (error) return <ApiErrorState message={error} />;
  if (!methods) return <div className="text-sm text-slate-600">Loading payment methods...</div>;
  return <section className="space-y-6"><div className="flex items-center justify-between"><h1 className="text-2xl font-semibold">Payment methods</h1><Link href="/dashboard/payments/methods/create" className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Create method</Link></div>{methods.length === 0 ? <EmptyState message="No payment methods yet." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Method</th><th>Business unit</th><th>Type</th><th>Status</th><th /></tr></thead><tbody>{methods.map((method) => <tr key={method.id} className="border-t border-slate-100"><td className="p-3">{method.name_en ?? method.name_ar}<br /><span className="text-slate-500">{method.key}</span></td><td>{method.business_unit?.slug ?? method.business_unit_id}</td><td>{method.type}</td><td>{method.is_active ? "active" : "inactive"}</td><td className="space-x-3 p-3 text-right"><Link className="text-teal-700" href={`/dashboard/payments/methods/${method.id}/edit`}>Edit</Link><button onClick={() => onToggle(method.id)} className="text-slate-700">{method.is_active ? "Disable" : "Enable"}</button></td></tr>)}</tbody></table></div>}</section>;
}

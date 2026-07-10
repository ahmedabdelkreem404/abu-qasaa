"use client";

import { listManualPaymentProofs } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { ManualPaymentProof } from "@/types/platform";
import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";

export default function ManualProofsPage() {
  const [proofs, setProofs] = useState<ManualPaymentProof[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  async function load(params?: URLSearchParams) {
    const response = await listManualPaymentProofs(params);
    setProofs(response.data);
  }

  useEffect(() => {
    let active = true;
    listManualPaymentProofs()
      .then((response) => { if (active) setProofs(response.data); })
      .catch(() => { if (active) setError("Could not load manual proofs."); });
    return () => { active = false; };
  }, []);

  async function onFilter(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const params = new URLSearchParams();
    ["business_unit_id", "status", "method_type", "order_number", "customer_phone"].forEach((key) => {
      const value = String(form.get(key) ?? "");
      if (value) params.set(key, value);
    });
    await load(params);
  }

  if (error) return <ApiErrorState message={error} />;
  if (!proofs) return <div className="text-sm text-slate-600">Loading manual proofs...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Manual payment proofs</h1><form onSubmit={onFilter} className="grid gap-3 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-5"><input name="business_unit_id" placeholder="Business unit ID" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="status" placeholder="Status" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="method_type" placeholder="Method type" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="order_number" placeholder="Order number" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="customer_phone" placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Filter</button></form>{proofs.length === 0 ? <EmptyState message="No manual proofs found." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Order</th><th>Method</th><th>Amount</th><th>Status</th><th /></tr></thead><tbody>{proofs.map((proof) => <tr key={proof.id} className="border-t border-slate-100"><td className="p-3">{proof.order?.order_number}<br /><span className="text-slate-500">{proof.order?.customer_phone}</span></td><td>{proof.payment_method?.name_en ?? proof.payment_method?.name_ar}</td><td>{proof.amount} {proof.order?.currency}</td><td>{proof.status}</td><td className="p-3 text-right"><Link className="text-teal-700" href={`/dashboard/payments/manual-proofs/${proof.id}`}>Review</Link></td></tr>)}</tbody></table></div>}</section>;
}

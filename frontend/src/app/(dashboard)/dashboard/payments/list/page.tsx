"use client";

import { listPayments } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { Payment } from "@/types/platform";
import { FormEvent, useEffect, useState } from "react";

export default function PaymentsListPage() {
  const [payments, setPayments] = useState<Payment[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  async function load(params?: URLSearchParams) { const response = await listPayments(params); setPayments(response.data); }
  useEffect(() => {
    let active = true;
    listPayments()
      .then((response) => { if (active) setPayments(response.data); })
      .catch(() => { if (active) setError("Could not load payments."); });
    return () => { active = false; };
  }, []);
  async function onFilter(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const params = new URLSearchParams();
    ["business_unit_id", "status", "method_type", "order_number", "customer_phone"].forEach((key) => { const value = String(form.get(key) ?? ""); if (value) params.set(key, value); });
    await load(params);
  }
  if (error) return <ApiErrorState message={error} />;
  if (!payments) return <div className="text-sm text-slate-600">Loading payments...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Payment records</h1><form onSubmit={onFilter} className="grid gap-3 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-5"><input name="business_unit_id" placeholder="Business unit ID" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="status" placeholder="Status" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="method_type" placeholder="Method type" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="order_number" placeholder="Order number" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="customer_phone" placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Filter</button></form>{payments.length === 0 ? <EmptyState message="No payment records found." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Order</th><th>Provider</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead><tbody>{payments.map((payment) => <tr key={payment.id} className="border-t border-slate-100"><td className="p-3">{payment.order?.order_number ?? "-"}</td><td>{payment.provider ?? "manual"}<br /><span className="text-slate-500">{payment.provider_status ?? ""}</span></td><td>{payment.payment_method?.name_en ?? payment.method_type}</td><td>{payment.amount} {payment.currency}</td><td>{payment.status}</td></tr>)}</tbody></table></div>}</section>;
}

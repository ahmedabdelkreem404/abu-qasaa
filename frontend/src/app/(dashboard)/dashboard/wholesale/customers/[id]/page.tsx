"use client";

import { approveWholesaleCustomer, assignWholesaleCustomerPriceList, getWholesaleCustomer, listPriceLists, rejectWholesaleCustomer, updateWholesaleCustomer } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { PriceList, WholesaleCustomer } from "@/types/platform";
import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";
import { useParams } from "next/navigation";

export default function WholesaleCustomerDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [customer, setCustomer] = useState<WholesaleCustomer | null>(null);
  const [lists, setLists] = useState<PriceList[]>([]);
  const [message, setMessage] = useState<string | null>(null);

  useEffect(() => {
    getWholesaleCustomer(id).then((r) => setCustomer(r.data)).catch(() => setMessage("Could not load wholesale customer."));
    listPriceLists(new URLSearchParams({ per_page: "100" })).then((r) => setLists(r.data.filter((list) => ["wholesale", "distributor", "special"].includes(list.type)))).catch(() => setLists([]));
  }, [id]);

  async function save(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const response = await updateWholesaleCustomer(id, {
      company_name: String(form.get("company_name") ?? "") || null,
      credit_limit: String(form.get("credit_limit") ?? "") || null,
      payment_terms: String(form.get("payment_terms") ?? "") || null,
      notes: String(form.get("notes") ?? "") || null,
    });
    setCustomer(response.data);
    setMessage("Customer updated.");
  }

  async function assign(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const priceListId = Number(new FormData(event.currentTarget).get("price_list_id"));
    const response = await assignWholesaleCustomerPriceList(id, priceListId);
    setCustomer(response.data);
    setMessage("Price list assigned.");
  }

  if (message && !customer) return <ApiErrorState message={message} />;
  if (!customer) return <p className="text-sm text-slate-600">Loading customer...</p>;

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold">{customer.name}</h1>
        <p className="text-sm text-slate-600">{customer.phone} · {customer.wholesale_status}</p>
      </div>
      {message ? <p className="rounded-md bg-slate-50 p-3 text-sm text-slate-700">{message}</p> : null}
      <div className="flex flex-wrap gap-3">
        <button onClick={() => approveWholesaleCustomer(id).then((r) => setCustomer(r.data))} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Approve</button>
        <button onClick={() => rejectWholesaleCustomer(id, "Rejected by dashboard.").then((r) => setCustomer(r.data))} className="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700">Reject</button>
        <Link href={`/dashboard/wholesale/customers/${id}/pricing`} className="rounded-md border border-slate-300 px-4 py-2 text-sm">Pricing preview</Link>
      </div>
      <div className="grid gap-4 md:grid-cols-2">
        <form onSubmit={save} className="grid gap-3 rounded-md border border-slate-200 bg-white p-5">
          <h2 className="font-semibold">Profile</h2>
          <input name="company_name" defaultValue={customer.company_name ?? ""} placeholder="Company name" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <input name="credit_limit" defaultValue={customer.credit_limit ?? ""} placeholder="Credit limit placeholder" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <input name="payment_terms" defaultValue={customer.payment_terms ?? ""} placeholder="Payment terms placeholder" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <textarea name="notes" defaultValue={customer.notes ?? ""} className="min-h-24 rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Save</button>
        </form>
        <form onSubmit={assign} className="grid content-start gap-3 rounded-md border border-slate-200 bg-white p-5">
          <h2 className="font-semibold">Price list</h2>
          <select name="price_list_id" defaultValue={customer.price_list_id ?? ""} className="rounded-md border border-slate-300 px-3 py-2 text-sm">{lists.map((list) => <option key={list.id} value={list.id}>{list.name} ({list.type})</option>)}</select>
          <button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Assign</button>
        </form>
      </div>
    </section>
  );
}

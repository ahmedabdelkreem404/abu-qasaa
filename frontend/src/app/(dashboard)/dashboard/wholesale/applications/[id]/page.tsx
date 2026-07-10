"use client";

import { approveWholesaleApplication, getWholesaleApplication, listPriceLists, rejectWholesaleApplication } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { PriceList, WholesaleApplication } from "@/types/platform";
import { FormEvent, useEffect, useState } from "react";
import { useParams } from "next/navigation";

export default function WholesaleApplicationDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [item, setItem] = useState<WholesaleApplication | null>(null);
  const [lists, setLists] = useState<PriceList[]>([]);
  const [message, setMessage] = useState<string | null>(null);

  useEffect(() => {
    getWholesaleApplication(id).then((r) => setItem(r.data)).catch(() => setMessage("Could not load application."));
    listPriceLists(new URLSearchParams({ per_page: "100" })).then((r) => setLists(r.data.filter((list) => ["wholesale", "distributor", "special"].includes(list.type)))).catch(() => setLists([]));
  }, [id]);

  async function approve(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const priceListId = Number(new FormData(event.currentTarget).get("price_list_id") || 0) || null;
    const response = await approveWholesaleApplication(id, { price_list_id: priceListId });
    setItem(response.data);
    setMessage("Application approved.");
  }

  async function reject(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const reason = String(new FormData(event.currentTarget).get("rejection_reason") ?? "");
    const response = await rejectWholesaleApplication(id, { rejection_reason: reason });
    setItem(response.data);
    setMessage("Application rejected.");
  }

  if (message && !item) return <ApiErrorState message={message} />;
  if (!item) return <p className="text-sm text-slate-600">Loading application...</p>;

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold">{item.applicant_name}</h1>
        <p className="text-sm text-slate-600">{item.phone} · {item.status}</p>
      </div>
      {message ? <p className="rounded-md bg-slate-50 p-3 text-sm text-slate-700">{message}</p> : null}
      <div className="rounded-md border border-slate-200 bg-white p-5 text-sm">
        <p>Company: {item.company_name ?? item.shop_name ?? "-"}</p>
        <p>Email: {item.email ?? "-"}</p>
        <p>Location: {[item.governorate, item.city].filter(Boolean).join(", ") || "-"}</p>
        <p>Message: {item.message ?? "-"}</p>
      </div>
      <div className="grid gap-4 md:grid-cols-2">
        <form onSubmit={approve} className="grid gap-3 rounded-md border border-slate-200 bg-white p-5">
          <h2 className="font-semibold">Approve</h2>
          <select name="price_list_id" className="rounded-md border border-slate-300 px-3 py-2 text-sm"><option value="">Default wholesale list</option>{lists.map((list) => <option key={list.id} value={list.id}>{list.name} ({list.type})</option>)}</select>
          <button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Approve</button>
        </form>
        <form onSubmit={reject} className="grid gap-3 rounded-md border border-slate-200 bg-white p-5">
          <h2 className="font-semibold">Reject</h2>
          <textarea name="rejection_reason" required className="min-h-24 rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <button className="w-fit rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700">Reject</button>
        </form>
      </div>
    </section>
  );
}

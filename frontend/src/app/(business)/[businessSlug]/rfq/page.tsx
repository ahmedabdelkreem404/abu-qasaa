"use client";

import { submitRfqRequest } from "@/api/client";
import { useParams } from "next/navigation";
import { useState } from "react";

export default function RfqPage() {
  const { businessSlug } = useParams<{ businessSlug: string }>();
  const [status, setStatus] = useState<string | null>(null);

  async function onSubmit(formData: FormData) {
    setStatus("Sending...");
    try {
      const response = await submitRfqRequest(businessSlug, {
        company_name: String(formData.get("company_name") ?? ""),
        contact_name: String(formData.get("contact_name") ?? ""),
        phone: String(formData.get("phone") ?? ""),
        email: String(formData.get("email") ?? ""),
        origin_country: String(formData.get("origin_country") ?? ""),
        destination_country: String(formData.get("destination_country") ?? ""),
        items: [{ item_name: String(formData.get("item_name") ?? "General cargo"), quantity: Number(formData.get("quantity") || 1), unit: String(formData.get("unit") ?? "item") }],
      });
      setStatus(`RFQ submitted: ${response.data.rfq_number}`);
    } catch {
      setStatus("RFQ could not be submitted.");
    }
  }

  return <section className="space-y-6"><h1 className="text-3xl font-semibold">Request quotation</h1><form action={onSubmit} className="grid gap-3 rounded-md border border-slate-200 bg-white p-5 md:grid-cols-2"><input name="company_name" placeholder="Company" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="contact_name" required placeholder="Contact name" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="phone" required placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="email" required type="email" placeholder="Email" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="origin_country" placeholder="Origin country" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="destination_country" placeholder="Destination country" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="item_name" required placeholder="Item" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="quantity" type="number" min={1} defaultValue={1} className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="unit" defaultValue="item" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><button className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white md:w-fit">Submit</button>{status ? <p className="text-sm text-slate-600">{status}</p> : null}</form></section>;
}

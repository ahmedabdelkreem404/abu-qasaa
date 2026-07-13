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

  return <section className="space-y-6"><div><p className="aq-eyebrow">{businessSlug}</p><h1 className="aq-title">Request quotation</h1><p className="aq-subtitle mt-2">Share cargo details and the team will respond with a structured quotation.</p></div><form action={onSubmit} className="aq-card aq-form-grid p-5"><input name="company_name" placeholder="Company" className="px-3 py-2.5 text-sm" /><input name="contact_name" required placeholder="Contact name" className="px-3 py-2.5 text-sm" /><input name="phone" required placeholder="Phone" className="px-3 py-2.5 text-sm" /><input name="email" required type="email" placeholder="Email" className="px-3 py-2.5 text-sm" /><input name="origin_country" placeholder="Origin country" className="px-3 py-2.5 text-sm" /><input name="destination_country" placeholder="Destination country" className="px-3 py-2.5 text-sm" /><input name="item_name" required placeholder="Item" className="px-3 py-2.5 text-sm" /><input name="quantity" type="number" min={1} defaultValue={1} className="px-3 py-2.5 text-sm" /><input name="unit" defaultValue="item" className="px-3 py-2.5 text-sm" /><button className="aq-btn md:w-fit">Submit</button>{status ? <p className="text-sm font-bold text-[var(--aq-muted)] md:col-span-2">{status}</p> : null}</form></section>;
}

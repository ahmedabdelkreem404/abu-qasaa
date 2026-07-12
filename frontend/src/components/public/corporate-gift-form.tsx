"use client";

import { submitCorporateGiftInquiry } from "@/api/client";
import { useState } from "react";

export function CorporateGiftForm({ businessSlug }: { businessSlug: string }) {
  const [status, setStatus] = useState<string | null>(null);

  async function onSubmit(formData: FormData) {
    setStatus("Sending...");
    try {
      await submitCorporateGiftInquiry(businessSlug, {
        company_name: String(formData.get("company_name") ?? ""),
        contact_name: String(formData.get("contact_name") ?? ""),
        phone: String(formData.get("phone") ?? ""),
        email: String(formData.get("email") ?? ""),
        quantity: Number(formData.get("quantity") || 1),
        budget_range: String(formData.get("budget_range") ?? ""),
        occasion: String(formData.get("occasion") ?? ""),
        message: String(formData.get("message") ?? ""),
      });
      setStatus("Inquiry submitted.");
    } catch {
      setStatus("Inquiry could not be submitted.");
    }
  }

  return (
    <form action={onSubmit} className="grid gap-3 rounded-md border border-slate-200 bg-white p-5 md:grid-cols-2">
      <input name="company_name" placeholder="Company name" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
      <input name="contact_name" required placeholder="Contact name" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
      <input name="phone" required placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
      <input name="email" type="email" placeholder="Email" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
      <input name="quantity" type="number" min={1} placeholder="Quantity" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
      <input name="budget_range" placeholder="Budget range" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
      <input name="occasion" placeholder="Occasion" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
      <textarea name="message" placeholder="Message" className="rounded-md border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
      <button className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white md:w-fit">Submit</button>
      {status ? <p className="text-sm text-slate-600">{status}</p> : null}
    </form>
  );
}

"use client";

import { getPublicRfqStatus } from "@/api/client";
import { useParams } from "next/navigation";
import { useState } from "react";

export default function RfqStatusPage() {
  const { businessSlug } = useParams<{ businessSlug: string }>();
  const [message, setMessage] = useState<string | null>(null);

  async function onSubmit(formData: FormData) {
    try {
      const response = await getPublicRfqStatus(businessSlug, String(formData.get("rfq_number")), String(formData.get("contact")));
      setMessage(`${response.data.rfq_number}: ${response.data.status}`);
    } catch {
      setMessage("RFQ was not found for that contact.");
    }
  }

  return <section className="space-y-6"><h1 className="text-3xl font-semibold">RFQ status</h1><form action={onSubmit} className="grid gap-3 rounded-md border border-slate-200 bg-white p-5 md:grid-cols-3"><input name="rfq_number" required placeholder="RFQ number" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input name="contact" required placeholder="Phone or email" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><button className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Check</button>{message ? <p className="text-sm text-slate-600 md:col-span-3">{message}</p> : null}</form></section>;
}

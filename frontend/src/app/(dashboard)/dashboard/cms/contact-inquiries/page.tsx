"use client";

import { listContactInquiries, updateContactInquiryStatus } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { ContactInquiry, InquiryStatus } from "@/types/platform";
import { useEffect, useState } from "react";

const statuses: InquiryStatus[] = ["new", "in_progress", "resolved", "spam", "archived"];

export default function ContactInquiriesPage() {
  const [inquiries, setInquiries] = useState<ContactInquiry[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    listContactInquiries()
      .then((response) => setInquiries(response.data))
      .catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Could not load contact inquiries."));
  }, []);

  async function onStatusChange(inquiry: ContactInquiry, status: InquiryStatus) {
    const response = await updateContactInquiryStatus(inquiry.id, status);
    setInquiries((current) => current?.map((item) => (item.id === inquiry.id ? response.data : item)) ?? null);
  }

  if (error) return <ApiErrorState message={error} />;
  if (!inquiries) return <div className="text-sm text-slate-600">Loading contact inquiries...</div>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Contact Inquiries</h1>
      {inquiries.length === 0 ? <EmptyState message="No contact inquiries yet." /> : null}
      <div className="grid gap-4">
        {inquiries.map((inquiry) => (
          <article key={inquiry.id} className="rounded-md border border-slate-200 bg-white p-5">
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h2 className="font-semibold">{inquiry.subject ?? "General inquiry"}</h2>
                <p className="text-sm text-slate-600">{inquiry.name} &middot; {inquiry.email ?? inquiry.phone ?? "No contact detail"}</p>
              </div>
              <select value={inquiry.status} onChange={(event) => onStatusChange(inquiry, event.target.value as InquiryStatus)} className="rounded-md border border-slate-300 px-3 py-2 text-sm">
                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
              </select>
            </div>
            <p className="mt-4 whitespace-pre-line text-sm text-slate-700">{inquiry.message}</p>
            <p className="mt-3 text-xs text-slate-500">{inquiry.source_page ?? "Unknown source"} &middot; {inquiry.created_at ?? ""}</p>
          </article>
        ))}
      </div>
    </section>
  );
}

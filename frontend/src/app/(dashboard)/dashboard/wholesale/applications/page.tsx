"use client";

import { listWholesaleApplications } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { WholesaleApplication } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function WholesaleApplicationsPage() {
  const [items, setItems] = useState<WholesaleApplication[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    listWholesaleApplications().then((response) => setItems(response.data)).catch(() => setError("Could not load wholesale applications."));
  }, []);

  if (error) return <ApiErrorState message={error} />;
  if (!items) return <p className="text-sm text-slate-600">Loading applications...</p>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Wholesale applications</h1>
      <div className="rounded-md border border-slate-200 bg-white p-4">
        <div className="grid gap-3 md:grid-cols-4">
          <input placeholder="Business unit" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <input placeholder="Status" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <input placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <input placeholder="Company" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>
      {items.length === 0 ? <EmptyState message="No wholesale applications yet." /> : (
        <div className="overflow-hidden rounded-md border border-slate-200 bg-white">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50"><tr><th className="p-3">Applicant</th><th>Phone</th><th>Business Unit</th><th>Status</th></tr></thead>
            <tbody>{items.map((item) => <tr key={item.id} className="border-t border-slate-100"><td className="p-3"><Link className="font-medium text-teal-700" href={`/dashboard/wholesale/applications/${item.id}`}>{item.applicant_name}</Link></td><td>{item.phone}</td><td>{item.business_unit?.slug ?? item.business_unit_id}</td><td>{item.status}</td></tr>)}</tbody>
          </table>
        </div>
      )}
    </section>
  );
}

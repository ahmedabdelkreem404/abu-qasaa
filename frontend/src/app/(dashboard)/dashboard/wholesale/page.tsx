"use client";

import { listWholesaleApplications, listWholesaleCustomers } from "@/api/client";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function WholesaleDashboardPage() {
  const [pending, setPending] = useState<number | null>(null);
  const [customers, setCustomers] = useState<number | null>(null);

  useEffect(() => {
    listWholesaleApplications(new URLSearchParams({ status: "pending" })).then((r) => setPending(r.meta.total)).catch(() => setPending(0));
    listWholesaleCustomers(new URLSearchParams({ wholesale_status: "approved" })).then((r) => setCustomers(r.meta.total)).catch(() => setCustomers(0));
  }, []);

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Wholesale</h1>
      <div className="grid gap-4 md:grid-cols-3">
        <Link href="/dashboard/wholesale/applications" className="rounded-md border border-slate-200 bg-white p-5">
          <p className="text-sm text-slate-600">Pending applications</p>
          <p className="mt-2 text-3xl font-semibold">{pending ?? "..."}</p>
        </Link>
        <Link href="/dashboard/wholesale/customers" className="rounded-md border border-slate-200 bg-white p-5">
          <p className="text-sm text-slate-600">Approved customers</p>
          <p className="mt-2 text-3xl font-semibold">{customers ?? "..."}</p>
        </Link>
        <Link href="/dashboard/catalog/price-lists" className="rounded-md border border-slate-200 bg-white p-5">
          <p className="text-sm text-slate-600">Price lists</p>
          <p className="mt-2 text-sm font-medium text-teal-700">Manage wholesale pricing</p>
        </Link>
      </div>
    </section>
  );
}

"use client";

import { listManualPaymentProofs, listPaymentMethods, listPayments } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function DashboardPaymentsPage() {
  const [counts, setCounts] = useState({ methods: 0, pendingProofs: 0, payments: 0 });
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([listPaymentMethods(), listManualPaymentProofs(new URLSearchParams({ status: "pending_review" })), listPayments()])
      .then(([methods, proofs, payments]) => setCounts({ methods: methods.meta.total, pendingProofs: proofs.meta.total, payments: payments.meta.total }))
      .catch(() => setError("Could not load payment overview."));
  }, []);

  if (error) return <ApiErrorState message={error} />;
  return <section className="space-y-6"><div><h1 className="text-2xl font-semibold">Payments</h1><p className="text-sm text-slate-600">Manual payments and Paymob online payment foundation.</p></div><div className="grid gap-4 md:grid-cols-4"><Card title="Payment methods" value={counts.methods} href="/dashboard/payments/methods" /><Card title="Pending manual proofs" value={counts.pendingProofs} href="/dashboard/payments/manual-proofs" /><Card title="Payments" value={counts.payments} href="/dashboard/payments/list" /><Card title="Paymob transactions" value={0} href="/dashboard/payments/paymob-transactions" /></div></section>;
}

function Card({ title, value, href }: { title: string; value: number; href: string }) {
  return <Link href={href} className="rounded-md border border-slate-200 bg-white p-5"><p className="text-sm text-slate-500">{title}</p><p className="mt-2 text-3xl font-semibold">{value}</p></Link>;
}

"use client";

import { getExecutiveReport } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { ExecutiveReport } from "@/types/platform";
import { useEffect, useState } from "react";

export default function ReportsPage() {
  const [report, setReport] = useState<ExecutiveReport | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => { getExecutiveReport().then((response) => setReport(response.data)).catch(() => setError("Could not load reports.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!report) return <p className="text-sm text-slate-600">Loading reports...</p>;

  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Reports</h1><div className="grid gap-4 md:grid-cols-3">{Object.entries(report).map(([key, value]) => <div key={key} className="rounded-md border border-slate-200 bg-white p-5"><p className="text-sm text-slate-500">{key.replaceAll("_", " ")}</p><p className="mt-2 text-2xl font-semibold">{String(value)}</p></div>)}</div><a href={`${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1"}/reports/commerce/orders/export`} className="inline-flex rounded-md border border-slate-300 px-4 py-2 text-sm font-medium">Export orders CSV</a></section>;
}

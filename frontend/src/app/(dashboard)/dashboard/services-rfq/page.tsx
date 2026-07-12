"use client";

import { listRfqRequests } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { RfqRequest } from "@/types/platform";
import { useEffect, useState } from "react";

export default function DashboardServicesRfqPage() {
  const [items, setItems] = useState<RfqRequest[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => { listRfqRequests().then((response) => setItems(response.data)).catch((event) => setError(event instanceof Error && event.name === "403" ? "Forbidden." : "Could not load RFQs.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!items) return <p className="text-sm text-slate-600">Loading RFQs...</p>;

  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Services / RFQ</h1>{items.length === 0 ? <EmptyState message="No RFQs yet." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><tbody>{items.map((item) => <tr key={item.id} className="border-t border-slate-100 first:border-t-0"><td className="p-3 font-medium">{item.rfq_number}</td><td>{item.contact_name}</td><td>{item.phone}</td><td>{item.status}</td></tr>)}</tbody></table></div>}</section>;
}

"use client";

import { listAuditLogs } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { AuditLog } from "@/types/platform";
import { useEffect, useState } from "react";

export default function AuditLogsPage() {
  const [items, setItems] = useState<AuditLog[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { listAuditLogs().then((response) => setItems(response.data)).catch(() => setError("Could not load audit logs.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!items) return <p className="text-sm text-slate-600">Loading audit logs...</p>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Audit Logs</h1>{items.length === 0 ? <EmptyState message="No audit logs yet." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><tbody>{items.map((item) => <tr key={item.id} className="border-t border-slate-100 first:border-t-0"><td className="p-3">{item.event}</td><td>{item.action}</td><td>{item.business_unit_id ?? "-"}</td><td>{item.created_at ?? "-"}</td></tr>)}</tbody></table></div>}</section>;
}

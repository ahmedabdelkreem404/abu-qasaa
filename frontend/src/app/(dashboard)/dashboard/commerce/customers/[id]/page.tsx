"use client";

import { getCustomer } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { Customer } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function CustomerDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { getCustomer(id).then((r) => setCustomer(r.data)).catch(() => setError("Could not load customer.")); }, [id]);
  if (error) return <ApiErrorState message={error} />;
  if (!customer) return <div className="text-sm text-slate-600">Loading customer...</div>;
  return <section className="space-y-6"><div><h1 className="text-2xl font-semibold">{customer.name}</h1><p className="text-sm text-slate-600">{customer.phone} · {customer.email ?? "No email"}</p></div><div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Addresses</h2>{customer.addresses?.length ? customer.addresses.map((address) => <p key={address.id} className="mt-2 text-sm">{address.street_address}, {address.city}</p>) : <p className="mt-2 text-sm text-slate-600">No addresses yet.</p>}</div><div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Order history</h2><p className="mt-2 text-sm text-slate-600">Order history summary will expand in a later phase.</p></div></section>;
}

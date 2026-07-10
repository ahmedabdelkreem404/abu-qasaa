"use client";

import { approveManualPaymentProof, getManualPaymentProof, rejectManualPaymentProof } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { ManualPaymentProof } from "@/types/platform";
import { useParams } from "next/navigation";
import { FormEvent, useEffect, useState } from "react";

export default function ManualProofDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [proof, setProof] = useState<ManualPaymentProof | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);
  useEffect(() => {
    let active = true;
    getManualPaymentProof(id)
      .then((response) => { if (active) setProof(response.data); })
      .catch(() => { if (active) setError("Could not load manual proof."); });
    return () => { active = false; };
  }, [id]);
  async function onApprove(event: FormEvent<HTMLFormElement>) { event.preventDefault(); const form = new FormData(event.currentTarget); const response = await approveManualPaymentProof(id, String(form.get("admin_notes") ?? "")); setProof(response.data); setMessage("Proof approved."); }
  async function onReject(event: FormEvent<HTMLFormElement>) { event.preventDefault(); const form = new FormData(event.currentTarget); const response = await rejectManualPaymentProof(id, String(form.get("rejected_reason") ?? ""), String(form.get("admin_notes") ?? "")); setProof(response.data); setMessage("Proof rejected."); }
  if (error) return <ApiErrorState message={error} />;
  if (!proof) return <div className="text-sm text-slate-600">Loading proof...</div>;
  return <section className="space-y-6"><div><h1 className="text-2xl font-semibold">Proof #{proof.id}</h1><p className="text-sm text-slate-600">{proof.status} · {proof.amount} {proof.order?.currency}</p></div>{message ? <div className="rounded-md border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900">{message}</div> : null}<div className="grid gap-4 md:grid-cols-3"><Box title="Order" body={`${proof.order?.order_number ?? "-"} / ${proof.order?.payment_status ?? "-"}`} /><Box title="Customer" body={`${proof.order?.customer_name ?? "-"} / ${proof.order?.customer_phone ?? "-"}`} /><Box title="Method" body={proof.payment_method?.name_en ?? proof.payment_method?.name_ar ?? "-"} /></div><div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Proof data</h2><div className="mt-3 grid gap-2 text-sm text-slate-700"><p>Payer: {proof.payer_name ?? "-"}</p><p>Sender account: {proof.sender_account ?? "-"}</p><p>Reference: {proof.transaction_reference ?? "-"}</p><p>Proof image/path: {proof.proof_image ?? "-"}</p><p>Notes: {proof.notes ?? "-"}</p><p>Admin notes: {proof.admin_notes ?? "-"}</p></div></div><div className="grid gap-4 md:grid-cols-2"><form onSubmit={onApprove} className="grid gap-3 rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Approve</h2><textarea name="admin_notes" placeholder="Admin notes" className="min-h-24 rounded-md border border-slate-300 px-3 py-2 text-sm" /><button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Approve proof</button></form><form onSubmit={onReject} className="grid gap-3 rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Reject</h2><input name="rejected_reason" placeholder="Rejected reason" required className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><textarea name="admin_notes" placeholder="Admin notes" className="min-h-24 rounded-md border border-slate-300 px-3 py-2 text-sm" /><button className="w-fit rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700">Reject proof</button></form></div></section>;
}

function Box({ title, body }: { title: string; body: string }) {
  return <div className="rounded-md border border-slate-200 bg-white p-4"><h2 className="text-sm font-medium text-slate-500">{title}</h2><p className="mt-2 font-medium">{body}</p></div>;
}

"use client";

import { getPublicOrderPaymentOptions, selectCashOnDelivery, submitManualPaymentProof } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { PaymentMethod, PublicOrderPaymentOptions } from "@/types/platform";
import { FormEvent, useEffect, useMemo, useState } from "react";

export function PublicPaymentPage({ businessSlug, orderNumber, initialPhone }: { businessSlug: string; orderNumber: string; initialPhone?: string }) {
  const [phone, setPhone] = useState(initialPhone ?? "");
  const [options, setOptions] = useState<PublicOrderPaymentOptions | null>(null);
  const [selectedKey, setSelectedKey] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!initialPhone) return;
    load(initialPhone);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialPhone]);

  const selectedMethod = useMemo(() => options?.payment_methods.find((method) => method.key === selectedKey) ?? options?.payment_methods[0], [options, selectedKey]);

  async function load(value: string) {
    setLoading(true);
    setError(null);
    try {
      const response = await getPublicOrderPaymentOptions(businessSlug, orderNumber, value);
      setOptions(response.data);
      setSelectedKey(response.data.payment_methods[0]?.key ?? "");
    } catch {
      setError("Could not load payment options for this order and phone.");
    } finally {
      setLoading(false);
    }
  }

  async function onPhoneSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    await load(phone);
  }

  async function onProofSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!selectedMethod || !options) return;
    const form = new FormData(event.currentTarget);
    setError(null);
    setSuccess(null);
    try {
      await submitManualPaymentProof(businessSlug, orderNumber, {
        phone,
        method_key: selectedMethod.key,
        amount: String(form.get("amount") ?? options.order.grand_total),
        payer_name: String(form.get("payer_name") ?? "") || null,
        sender_account: String(form.get("sender_account") ?? "") || null,
        transaction_reference: String(form.get("transaction_reference") ?? "") || null,
        proof_image: String(form.get("proof_image") ?? "") || null,
        notes: String(form.get("notes") ?? "") || null,
      });
      setSuccess("Payment proof submitted and pending review.");
      await load(phone);
    } catch {
      setError("Could not submit proof. Check the amount, method, and phone number.");
    }
  }

  async function onCod() {
    setError(null);
    setSuccess(null);
    try {
      await selectCashOnDelivery(businessSlug, orderNumber, phone);
      setSuccess("Cash on delivery selected.");
      await load(phone);
    } catch {
      setError("Could not select cash on delivery.");
    }
  }

  if (!options) {
    return <form onSubmit={onPhoneSubmit} className="grid max-w-md gap-3 rounded-md border border-slate-200 bg-white p-5"><h1 className="text-2xl font-semibold">Order payment</h1>{error ? <ApiErrorState message={error} /> : null}<input value={phone} onChange={(event) => setPhone(event.target.value)} name="phone" placeholder="Phone used on the order" className="rounded-md border border-slate-300 px-3 py-2" required /><button disabled={loading} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">{loading ? "Loading..." : "Load payment options"}</button></form>;
  }

  return <section className="space-y-6"><div><h1 className="text-3xl font-semibold">Pay order {options.order.order_number}</h1><p className="text-sm text-slate-600">{options.order.grand_total} {options.order.currency} · {options.order.payment_status}</p></div>{success ? <div className="rounded-md border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900">{success}</div> : null}{error ? <ApiErrorState message={error} /> : null}<div className="grid gap-4 lg:grid-cols-[280px_1fr]"><div className="grid gap-2">{options.payment_methods.length === 0 ? <p className="rounded-md border border-slate-200 bg-white p-4 text-sm text-slate-600">No manual payment methods are available. No Paymob/card payments yet.</p> : options.payment_methods.map((method) => <button key={method.id} onClick={() => setSelectedKey(method.key)} className={`rounded-md border px-4 py-3 text-left text-sm ${selectedMethod?.key === method.key ? "border-teal-700 bg-teal-50" : "border-slate-200 bg-white"}`}><span className="font-medium">{method.name_en ?? method.name_ar}</span><span className="mt-1 block text-xs text-slate-500">{method.type.replaceAll("_", " ")}</span></button>)}</div>{selectedMethod ? <PaymentMethodPanel method={selectedMethod} total={options.order.grand_total} currency={options.order.currency} onProofSubmit={onProofSubmit} onCod={onCod} /> : null}</div>{options.proofs.length > 0 ? <div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Submitted proofs</h2><div className="mt-3 grid gap-2">{options.proofs.map((proof) => <p key={proof.id} className="text-sm text-slate-700">{proof.payment_method?.name_en ?? proof.payment_method?.name_ar}: {proof.status} · {proof.amount} {options.order.currency}</p>)}</div></div> : null}</section>;
}

function PaymentMethodPanel({ method, total, currency, onProofSubmit, onCod }: { method: PaymentMethod; total: string; currency: string; onProofSubmit: (event: FormEvent<HTMLFormElement>) => void; onCod: () => void }) {
  const isCod = method.type === "cash_on_delivery";
  return <div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="text-xl font-semibold">{method.name_en ?? method.name_ar}</h2><div className="mt-3 rounded-md bg-slate-50 p-4 text-sm text-slate-700"><p>{method.instructions_en ?? method.instructions_ar ?? "Follow the manual payment instructions, then submit proof for admin review."}</p>{method.destination_account ? <p className="mt-2 font-medium">Destination: {method.destination_account}</p> : null}<p className="mt-2 text-xs text-slate-500">No Paymob/card payments or automatic verification in this phase.</p></div>{isCod ? <button onClick={onCod} className="mt-4 rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Confirm cash on delivery</button> : <form onSubmit={onProofSubmit} className="mt-4 grid gap-3"><Input name="amount" label={`Amount (${currency})`} defaultValue={total} required /><Input name="payer_name" label="Payer name" /><Input name="sender_account" label="Sender account" /><Input name="transaction_reference" label="Transaction reference" /><Input name="proof_image" label="Proof image/path" /><label className="grid gap-1 text-sm">Notes<textarea name="notes" className="min-h-24 rounded-md border border-slate-300 px-3 py-2" /></label><button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Submit proof</button></form>}</div>;
}

function Input(props: React.InputHTMLAttributes<HTMLInputElement> & { label: string }) {
  const { label, ...inputProps } = props;
  return <label className="grid gap-1 text-sm">{label}<input {...inputProps} className="rounded-md border border-slate-300 px-3 py-2" /></label>;
}

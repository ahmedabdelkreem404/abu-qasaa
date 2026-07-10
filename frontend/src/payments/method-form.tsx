"use client";

import { createPaymentMethod, getPaymentMethod, listBusinessUnits, updatePaymentMethod } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { BusinessUnit, PaymentMethod, PaymentMethodType } from "@/types/platform";
import { useRouter } from "next/navigation";
import { FormEvent, useEffect, useState } from "react";

const types: PaymentMethodType[] = ["vodafone_cash", "instapay", "bank_transfer", "cash_on_delivery", "paymob_placeholder"];

export function PaymentMethodForm({ id }: { id?: string }) {
  const router = useRouter();
  const [method, setMethod] = useState<PaymentMethod | null>(null);
  const [businessUnits, setBusinessUnits] = useState<BusinessUnit[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    listBusinessUnits().then((response) => setBusinessUnits(response.data)).catch(() => setError("Could not load business units."));
    if (id) getPaymentMethod(id).then((response) => setMethod(response.data)).catch(() => setError("Could not load payment method."));
  }, [id]);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setSaving(true);
    setError(null);
    const payload = {
      business_unit_id: Number(form.get("business_unit_id")),
      key: String(form.get("key") ?? ""),
      type: String(form.get("type") ?? "vodafone_cash") as PaymentMethodType,
      name_ar: String(form.get("name_ar") ?? ""),
      name_en: String(form.get("name_en") ?? "") || null,
      instructions_ar: String(form.get("instructions_ar") ?? "") || null,
      instructions_en: String(form.get("instructions_en") ?? "") || null,
      destination_account: String(form.get("destination_account") ?? "") || null,
      destination_account_name: String(form.get("destination_account_name") ?? "") || null,
      is_active: form.get("is_active") === "on",
      sort_order: Number(form.get("sort_order") ?? 0),
    };
    try {
      if (id) await updatePaymentMethod(id, payload);
      else await createPaymentMethod(payload);
      router.push("/dashboard/payments/methods");
    } catch {
      setError("Could not save payment method. Check key uniqueness and permissions.");
    } finally {
      setSaving(false);
    }
  }

  if (id && !method && !error) return <div className="text-sm text-slate-600">Loading method...</div>;

  return <form onSubmit={onSubmit} className="grid gap-4 rounded-md border border-slate-200 bg-white p-5">{error ? <ApiErrorState message={error} /> : null}<label className="grid gap-1 text-sm">Business unit<select name="business_unit_id" defaultValue={method?.business_unit_id} className="rounded-md border border-slate-300 px-3 py-2" required>{businessUnits.map((unit) => <option key={unit.id} value={unit.id}>{unit.name_en ?? unit.slug}</option>)}</select></label><Input name="key" label="Key" defaultValue={method?.key} required /><label className="grid gap-1 text-sm">Type<select name="type" defaultValue={method?.type ?? "vodafone_cash"} className="rounded-md border border-slate-300 px-3 py-2">{types.map((type) => <option key={type} value={type}>{type}</option>)}</select></label><Input name="name_ar" label="Name AR" defaultValue={method?.name_ar} required /><Input name="name_en" label="Name EN" defaultValue={method?.name_en ?? ""} /><Input name="destination_account" label="Destination account" defaultValue={method?.destination_account ?? ""} /><Input name="destination_account_name" label="Destination account name" defaultValue={method?.destination_account_name ?? ""} /><label className="grid gap-1 text-sm">Instructions AR<textarea name="instructions_ar" defaultValue={method?.instructions_ar ?? ""} className="min-h-24 rounded-md border border-slate-300 px-3 py-2" /></label><label className="grid gap-1 text-sm">Instructions EN<textarea name="instructions_en" defaultValue={method?.instructions_en ?? ""} className="min-h-24 rounded-md border border-slate-300 px-3 py-2" /></label><Input name="sort_order" type="number" label="Sort order" defaultValue={method?.sort_order ?? 0} /><label className="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" defaultChecked={method?.is_active ?? true} /> Active</label><button disabled={saving} className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">{saving ? "Saving..." : "Save method"}</button></form>;
}

function Input(props: React.InputHTMLAttributes<HTMLInputElement> & { label: string }) {
  const { label, ...inputProps } = props;
  return <label className="grid gap-1 text-sm">{label}<input {...inputProps} className="rounded-md border border-slate-300 px-3 py-2" /></label>;
}

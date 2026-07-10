"use client";

import {
  getWholesaleStatus,
  listPublicWholesaleProducts,
  requestWholesaleAccess,
  submitWholesaleApplication,
} from "@/api/client";
import type { WholesaleAccess, WholesalePricing } from "@/types/platform";
import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";

export function wholesaleKey(slug: string) {
  return `abu_qasaa_wholesale_${slug}`;
}

export function getStoredWholesaleAccess(slug: string): { phone: string; token: string } | null {
  if (typeof window === "undefined") return null;
  const raw = window.localStorage.getItem(wholesaleKey(slug));
  if (!raw) return null;
  try {
    const parsed = JSON.parse(raw) as { phone?: string; token?: string };
    return parsed.phone && parsed.token ? { phone: parsed.phone, token: parsed.token } : null;
  } catch {
    return null;
  }
}

export function WholesaleApplicationForm({ businessSlug }: { businessSlug: string }) {
  const [status, setStatus] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setError(null);
    try {
      await submitWholesaleApplication(businessSlug, {
        applicant_name: String(form.get("applicant_name") ?? ""),
        phone: String(form.get("phone") ?? ""),
        email: String(form.get("email") ?? "") || null,
        company_name: String(form.get("company_name") ?? "") || null,
        shop_name: String(form.get("shop_name") ?? "") || null,
        tax_number: String(form.get("tax_number") ?? "") || null,
        commercial_record: String(form.get("commercial_record") ?? "") || null,
        governorate: String(form.get("governorate") ?? "") || null,
        city: String(form.get("city") ?? "") || null,
        address: String(form.get("address") ?? "") || null,
        message: String(form.get("message") ?? "") || null,
      });
      setStatus("Application submitted. Our team will review it soon.");
      event.currentTarget.reset();
    } catch {
      setError("Wholesale applications are not available for this business unit.");
    }
  }

  return (
    <form onSubmit={onSubmit} className="grid gap-4 rounded-md border border-slate-200 bg-white p-5">
      {status ? <p className="rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">{status}</p> : null}
      {error ? <p className="rounded-md bg-red-50 p-3 text-sm text-red-700">{error}</p> : null}
      <Input name="applicant_name" label="Applicant name" required />
      <Input name="phone" label="Phone" required />
      <Input name="email" label="Email" type="email" />
      <Input name="company_name" label="Company name" />
      <Input name="shop_name" label="Shop name" />
      <div className="grid gap-4 md:grid-cols-2">
        <Input name="tax_number" label="Tax number" />
        <Input name="commercial_record" label="Commercial record" />
      </div>
      <div className="grid gap-4 md:grid-cols-2">
        <Input name="governorate" label="Governorate" />
        <Input name="city" label="City" />
      </div>
      <label className="grid gap-1 text-sm">Address<textarea name="address" className="min-h-20 rounded-md border border-slate-300 px-3 py-2" /></label>
      <label className="grid gap-1 text-sm">Message<textarea name="message" className="min-h-24 rounded-md border border-slate-300 px-3 py-2" /></label>
      <button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Submit application</button>
    </form>
  );
}

export function WholesaleStatusLookup({ businessSlug }: { businessSlug: string }) {
  const [result, setResult] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const phone = String(new FormData(event.currentTarget).get("phone") ?? "");
    setError(null);
    try {
      const response = await getWholesaleStatus(businessSlug, phone);
      setResult(response.data.type === "none" ? "No wholesale application was found for this phone." : `Status: ${response.data.status}`);
    } catch {
      setError("Could not check wholesale status.");
    }
  }

  return (
    <form onSubmit={onSubmit} className="grid max-w-xl gap-4 rounded-md border border-slate-200 bg-white p-5">
      {result ? <p className="rounded-md bg-slate-50 p-3 text-sm text-slate-700">{result}</p> : null}
      {error ? <p className="text-sm text-red-600">{error}</p> : null}
      <Input name="phone" label="Phone" required />
      <button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Check status</button>
    </form>
  );
}

export function WholesaleAccessPanel({ businessSlug }: { businessSlug: string }) {
  const [access, setAccess] = useState<WholesaleAccess | null>(null);
  const [message, setMessage] = useState<string | null>(null);

  useEffect(() => {
    const stored = getStoredWholesaleAccess(businessSlug);
    if (stored) {
      queueMicrotask(() => setAccess({ access_method: "phone_token", token: stored.token, customer: { id: 0, business_unit_id: 0, type: "shop", name: "Wholesale customer", phone: stored.phone, wholesale_status: "approved" } }));
    }
  }, [businessSlug]);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const phone = String(new FormData(event.currentTarget).get("phone") ?? "");
    setMessage(null);
    try {
      const response = await requestWholesaleAccess(businessSlug, phone);
      window.localStorage.setItem(wholesaleKey(businessSlug), JSON.stringify({ phone, token: response.data.token }));
      setAccess(response.data);
      setMessage("Wholesale access is active on this device.");
    } catch {
      setMessage("Wholesale prices are available only to approved customers.");
    }
  }

  return (
    <div className="rounded-md border border-slate-200 bg-white p-5">
      <h2 className="font-semibold">Wholesale access</h2>
      {access ? <p className="mt-2 text-sm text-emerald-700">Approved access found for {access.customer.phone}.</p> : <p className="mt-2 text-sm text-slate-600">Enter the approved customer phone to unlock wholesale prices on this device.</p>}
      {message ? <p className="mt-3 text-sm text-slate-700">{message}</p> : null}
      <form onSubmit={onSubmit} className="mt-4 flex flex-wrap gap-3">
        <input name="phone" placeholder="Approved phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
        <button className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Request access</button>
      </form>
    </div>
  );
}

export function WholesaleProducts({ businessSlug }: { businessSlug: string }) {
  const [products, setProducts] = useState<WholesalePricing[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const access = getStoredWholesaleAccess(businessSlug);
    if (!access) {
      queueMicrotask(() => setError("Wholesale prices are available only to approved customers."));
      return;
    }
    listPublicWholesaleProducts(businessSlug, access)
      .then((response) => setProducts(response.data))
      .catch(() => setError("Could not load wholesale products."));
  }, [businessSlug]);

  if (error) return <div className="space-y-4"><WholesaleAccessPanel businessSlug={businessSlug} /><p className="text-sm text-slate-600">{error}</p></div>;
  if (!products) return <p className="text-sm text-slate-600">Loading wholesale products...</p>;
  if (products.length === 0) return <p className="rounded-md border border-slate-200 bg-white p-5 text-sm text-slate-600">No wholesale products are available yet.</p>;

  return (
    <div className="grid gap-4 md:grid-cols-3">
      {products.map((product) => (
        <Link key={product.product_id} href={`/${businessSlug}/wholesale/products/${product.product_slug}`} className="rounded-md border border-slate-200 bg-white p-5">
          <h2 className="font-semibold">{product.name_en ?? product.name_ar}</h2>
          <p className="mt-2 text-sm text-slate-600">{product.sku ?? "Wholesale item"}</p>
          <p className="mt-3 text-sm font-medium text-teal-700">{product.wholesale_price} {product.currency}</p>
          <p className="mt-1 text-xs text-slate-500">Minimum quantity: {product.min_quantity_applied}</p>
        </Link>
      ))}
    </div>
  );
}

function Input(props: React.InputHTMLAttributes<HTMLInputElement> & { label: string }) {
  const { label, ...inputProps } = props;
  return <label className="grid gap-1 text-sm">{label}<input {...inputProps} className="rounded-md border border-slate-300 px-3 py-2" /></label>;
}

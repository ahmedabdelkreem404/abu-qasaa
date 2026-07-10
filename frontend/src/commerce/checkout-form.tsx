"use client";

import { submitCheckout } from "@/api/client";
import { cartKey } from "@/commerce/cart-tools";
import Link from "next/link";
import { FormEvent, useState } from "react";

export function CheckoutForm({ businessSlug }: { businessSlug: string }) {
  const [orderNumber, setOrderNumber] = useState<string | null>(null);
  const [phone, setPhone] = useState("");
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const token = window.localStorage.getItem(cartKey(businessSlug));
    if (!token) {
      setError("Cart is empty.");
      return;
    }
    const customerPhone = String(form.get("phone") ?? "");
    setPhone(customerPhone);
    try {
      const response = await submitCheckout(businessSlug, {
        session_token: token,
        customer: {
          name: String(form.get("name") ?? ""),
          phone: customerPhone,
          email: String(form.get("email") ?? "") || null,
        },
        shipping_address: {
          recipient_name: String(form.get("recipient_name") ?? ""),
          phone: String(form.get("shipping_phone") ?? ""),
          governorate: String(form.get("governorate") ?? ""),
          city: String(form.get("city") ?? ""),
          street_address: String(form.get("street_address") ?? ""),
        },
        notes: String(form.get("notes") ?? "") || null,
      });
      window.localStorage.removeItem(cartKey(businessSlug));
      setOrderNumber(response.data.order_number);
    } catch {
      setError("Could not submit checkout. No online payment is available yet.");
    }
  }

  if (orderNumber) {
    return <div className="rounded-md border border-teal-200 bg-teal-50 p-6"><h1 className="text-2xl font-semibold">Order submitted</h1><p className="mt-2">Your order has been submitted and is pending confirmation.</p><Link href={`/${businessSlug}/orders/${orderNumber}?phone=${encodeURIComponent(phone)}`} className="mt-4 inline-flex text-teal-700">Track order {orderNumber}</Link></div>;
  }

  return <form onSubmit={onSubmit} className="grid gap-4 rounded-md border border-slate-200 bg-white p-5">
    {error ? <p className="text-sm text-red-600">{error}</p> : null}
    <p className="text-sm text-slate-600">No online payment yet. Your order will be pending confirmation.</p>
    <Input name="name" label="Name" required />
    <Input name="phone" label="Phone" required />
    <Input name="email" label="Email" type="email" />
    <Input name="recipient_name" label="Recipient name" required />
    <Input name="shipping_phone" label="Shipping phone" required />
    <Input name="governorate" label="Governorate" />
    <Input name="city" label="City" />
    <Input name="street_address" label="Street address" required />
    <label className="grid gap-1 text-sm">Notes<textarea name="notes" className="min-h-24 rounded-md border border-slate-300 px-3 py-2" /></label>
    <button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Submit order</button>
  </form>;
}

function Input(props: React.InputHTMLAttributes<HTMLInputElement> & { label: string }) {
  const { label, ...inputProps } = props;
  return <label className="grid gap-1 text-sm">{label}<input {...inputProps} className="rounded-md border border-slate-300 px-3 py-2" /></label>;
}

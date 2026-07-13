"use client";

import { submitCheckout } from "@/api/client";
import { cartKey } from "@/commerce/cart-tools";
import { getStoredWholesaleAccess } from "@/commerce/wholesale-tools";
import type { Order } from "@/types/platform";
import Link from "next/link";
import { FormEvent, useState } from "react";

export function CheckoutForm({ businessSlug }: { businessSlug: string }) {
  const [order, setOrder] = useState<Order | null>(null);
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
        wholesale_phone: getStoredWholesaleAccess(businessSlug)?.phone,
        wholesale_token: getStoredWholesaleAccess(businessSlug)?.token,
      });
      window.localStorage.removeItem(cartKey(businessSlug));
      setOrder(response.data);
    } catch {
      setError("Could not submit checkout.");
    }
  }

  if (order) {
    return <div className="aq-card border-[color:rgb(11_122_69_/_0.22)] bg-emerald-50 p-6"><h1 className="text-2xl font-black">Order submitted</h1><div className="mt-3 grid gap-1 text-sm text-[var(--aq-ink-2)]"><p>Order: <span className="font-bold">{order.order_number}</span></p><p>Total: <span className="font-bold">{order.grand_total} {order.currency}</span></p><p>Payment status: <span className="font-bold">{order.payment_status}</span></p></div><div className="mt-4 flex flex-wrap gap-3"><Link href={`/${businessSlug}/orders/${order.order_number}/payment?phone=${encodeURIComponent(phone)}`} className="aq-btn">Choose payment method</Link><Link href={`/${businessSlug}/orders/${order.order_number}?phone=${encodeURIComponent(phone)}`} className="aq-btn-secondary">Track order</Link></div></div>;
  }

  return <form onSubmit={onSubmit} className="aq-card aq-form-grid p-5">
    {error ? <p className="text-sm font-bold text-red-600 md:col-span-2">{error}</p> : null}
    <p className="text-sm leading-7 text-[var(--aq-muted)] md:col-span-2">Manual payments and cash on delivery are available after the order is submitted. Wholesale carts keep their approved price snapshots.</p>
    <Input name="name" label="Name" required />
    <Input name="phone" label="Phone" required />
    <Input name="email" label="Email" type="email" />
    <Input name="recipient_name" label="Recipient name" required />
    <Input name="shipping_phone" label="Shipping phone" required />
    <Input name="governorate" label="Governorate" />
    <Input name="city" label="City" />
    <Input name="street_address" label="Street address" required />
    <label className="grid gap-1 text-sm md:col-span-2">Notes<textarea name="notes" className="min-h-24 px-3 py-2" /></label>
    <button className="aq-btn md:w-fit">Submit order</button>
  </form>;
}

function Input(props: React.InputHTMLAttributes<HTMLInputElement> & { label: string }) {
  const { label, ...inputProps } = props;
  return <label className="grid gap-1 text-sm font-bold text-[var(--aq-ink-2)]">{label}<input {...inputProps} className="px-3 py-2.5 font-normal" /></label>;
}

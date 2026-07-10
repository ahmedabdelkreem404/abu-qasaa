"use client";

import { addCartItem, clearCart, getCart, getOrCreateCart, removeCartItem, updateCartItem } from "@/api/client";
import type { Cart, Product } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export function cartKey(slug: string) {
  return `abu_qasaa_cart_${slug}`;
}

export function AddToCartButton({ businessSlug, product }: { businessSlug: string; product: Product }) {
  const [message, setMessage] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function onAdd() {
    setLoading(true);
    setMessage(null);
    try {
      const token = window.localStorage.getItem(cartKey(businessSlug));
      const cart = await getOrCreateCart(businessSlug, token);
      window.localStorage.setItem(cartKey(businessSlug), cart.data.session_token);
      await addCartItem(businessSlug, cart.data.session_token, { product_id: product.id, quantity: 1 });
      setMessage("Added to cart.");
    } catch {
      setMessage("Ordering currently unavailable.");
    } finally {
      setLoading(false);
    }
  }

  if (!product.base_price) {
    return <p className="mt-3 text-sm text-slate-500">Ordering currently unavailable</p>;
  }

  return (
    <div className="mt-4 space-y-2">
      <button onClick={onAdd} disabled={loading} className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white disabled:opacity-60">
        {loading ? "Adding..." : "Add to cart"}
      </button>
      {message ? <p className="text-sm text-slate-600">{message} <Link href={`/${businessSlug}/cart`} className="text-teal-700">View cart</Link></p> : null}
    </div>
  );
}

export function CartManager({ businessSlug }: { businessSlug: string }) {
  const [cart, setCart] = useState<Cart | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const token = window.localStorage.getItem(cartKey(businessSlug));
    if (!token) {
      return;
    }
    getCart(businessSlug, token).then((response) => setCart(response.data)).catch(() => setError("Cart could not be loaded."));
  }, [businessSlug]);

  async function refresh(next: Promise<{ data: Cart }>) {
    const response = await next;
    setCart(response.data);
  }

  if (error) return <p className="text-sm text-red-600">{error}</p>;
  if (!cart || (cart.items ?? []).length === 0) return <p className="rounded-md border border-slate-200 bg-white p-6 text-sm text-slate-600">Your cart is empty.</p>;

  return (
    <section className="space-y-6">
      <div className="grid gap-4">
        {cart.items?.map((item) => (
          <div key={item.id} className="flex flex-wrap items-center justify-between gap-3 rounded-md border border-slate-200 bg-white p-4">
            <div>
              <h2 className="font-semibold">{item.product_name_en ?? item.product_name_ar}</h2>
              <p className="text-sm text-slate-600">{item.sku ?? ""} · {item.unit_price} {cart.currency}</p>
            </div>
            <div className="flex items-center gap-2">
              <input type="number" min={1} defaultValue={item.quantity} onBlur={(event) => refresh(updateCartItem(businessSlug, cart.session_token, item.id, Number(event.currentTarget.value)))} className="w-20 rounded-md border border-slate-300 px-2 py-1 text-sm" />
              <button onClick={() => refresh(removeCartItem(businessSlug, cart.session_token, item.id))} className="rounded-md border border-slate-300 px-3 py-2 text-sm">Remove</button>
            </div>
          </div>
        ))}
      </div>
      <div className="rounded-md border border-slate-200 bg-white p-5">
        <p className="text-sm text-slate-600">Subtotal: {cart.subtotal} {cart.currency}</p>
        <p className="mt-2 text-xl font-semibold">Total: {cart.grand_total} {cart.currency}</p>
        <div className="mt-4 flex gap-2">
          <Link href={`/${businessSlug}/checkout`} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Checkout</Link>
          <button onClick={() => refresh(clearCart(businessSlug, cart.session_token))} className="rounded-md border border-slate-300 px-4 py-2 text-sm">Clear cart</button>
        </div>
      </div>
    </section>
  );
}

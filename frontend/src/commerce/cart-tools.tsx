"use client";

import { addCartItem, clearCart, getCart, getOrCreateCart, removeCartItem, updateCartItem } from "@/api/client";
import { getStoredWholesaleAccess } from "@/commerce/wholesale-tools";
import type { Cart, Product } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export function cartKey(slug: string) {
  return `abu_qasaa_cart_${slug}`;
}

export function AddToCartButton({ businessSlug, product, disabled = false }: { businessSlug: string; product: Product; disabled?: boolean }) {
  const [message, setMessage] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function onAdd() {
    setLoading(true);
    setMessage(null);
    try {
      const token = window.localStorage.getItem(cartKey(businessSlug));
      const cart = await getOrCreateCart(businessSlug, token);
      window.localStorage.setItem(cartKey(businessSlug), cart.data.session_token);
      const access = getStoredWholesaleAccess(businessSlug);
      await addCartItem(businessSlug, cart.data.session_token, {
        product_id: product.id,
        quantity: access ? Math.max(1, product.min_order_quantity, 12) : 1,
        wholesale_phone: access?.phone,
        wholesale_token: access?.token,
      });
      setMessage("Added to cart.");
    } catch {
      setMessage("Ordering currently unavailable.");
    } finally {
      setLoading(false);
    }
  }

  if (disabled) {
    return <p className="mt-3 text-sm font-medium text-amber-700">Out of stock</p>;
  }

  if (!product.base_price) {
    return <p className="mt-3 text-sm text-slate-500">Ordering currently unavailable</p>;
  }

  return (
    <div className="mt-4 space-y-2">
      <button onClick={onAdd} disabled={loading || disabled} className="aq-btn min-h-10 px-3 py-2 disabled:opacity-60">
        {loading ? "Adding..." : "Add to cart"}
      </button>
      {message ? <p className="text-sm text-[var(--aq-muted)]">{message} <Link href={`/${businessSlug}/cart`} className="font-bold text-[var(--aq-primary)]">View cart</Link></p> : null}
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

  if (error) return <p className="text-sm font-bold text-red-600">{error}</p>;
  if (!cart || (cart.items ?? []).length === 0) return <p className="aq-card-muted p-6 text-sm font-bold text-[var(--aq-muted)]">Your cart is empty.</p>;

  return (
    <section className="space-y-6">
      <div className="grid gap-4">
        {cart.items?.map((item) => (
          <div key={item.id} className="aq-card flex flex-wrap items-center justify-between gap-3 p-4">
            <div>
              <h2 className="font-semibold">{item.product_name_en ?? item.product_name_ar}</h2>
              <p className="text-sm text-[var(--aq-muted)]">{item.sku ?? ""} · {item.unit_price} {cart.currency}</p>
              {item.metadata_json?.price_audience && item.metadata_json.price_audience !== "retail" ? <p className="mt-1 text-xs font-bold text-[var(--aq-primary)]">Wholesale · minimum {String(item.metadata_json.min_quantity_applied ?? 1)}</p> : null}
              {typeof item.metadata_json?.bundle === "object" && item.metadata_json.bundle !== null ? <p className="mt-1 text-xs font-bold text-[var(--aq-gold-2)]">Bundle · {String((item.metadata_json.bundle as { name_en?: string; name_ar?: string }).name_en ?? (item.metadata_json.bundle as { name_ar?: string }).name_ar ?? "Gift box")}</p> : null}
            </div>
            <div className="flex items-center gap-2">
              <input type="number" min={1} defaultValue={item.quantity} onBlur={(event) => refresh(updateCartItem(businessSlug, cart.session_token, item.id, Number(event.currentTarget.value)))} className="w-20 px-2 py-2 text-sm" />
              <button onClick={() => refresh(removeCartItem(businessSlug, cart.session_token, item.id))} className="aq-btn-secondary min-h-10 px-3 py-2">Remove</button>
            </div>
          </div>
        ))}
      </div>
      <div className="aq-card p-5">
        <p className="text-sm text-[var(--aq-muted)]">Subtotal: {cart.subtotal} {cart.currency}</p>
        {cart.items?.some((item) => item.metadata_json?.price_audience && item.metadata_json.price_audience !== "retail") ? <p className="mt-1 text-sm font-bold text-[var(--aq-primary)]">Wholesale cart</p> : null}
        <p className="mt-2 text-2xl font-black">Total: {cart.grand_total} {cart.currency}</p>
        <div className="mt-4 flex gap-2">
          <Link href={`/${businessSlug}/checkout`} className="aq-btn">Checkout</Link>
          <button onClick={() => refresh(clearCart(businessSlug, cart.session_token))} className="aq-btn-secondary">Clear cart</button>
        </div>
      </div>
    </section>
  );
}

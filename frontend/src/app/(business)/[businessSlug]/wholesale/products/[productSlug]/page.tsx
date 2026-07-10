"use client";

import { addCartItem, getOrCreateCart, getPublicWholesaleProduct } from "@/api/client";
import { cartKey } from "@/commerce/cart-tools";
import { getStoredWholesaleAccess } from "@/commerce/wholesale-tools";
import type { WholesalePricing } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function WholesaleProductDetailPage() {
  const { businessSlug, productSlug } = useParams<{ businessSlug: string; productSlug: string }>();
  const [product, setProduct] = useState<WholesalePricing | null>(null);
  const [message, setMessage] = useState<string | null>(null);

  useEffect(() => {
    const access = getStoredWholesaleAccess(businessSlug);
    if (!access) {
      queueMicrotask(() => setMessage("Wholesale prices are available only to approved customers."));
      return;
    }
    getPublicWholesaleProduct(businessSlug, productSlug, access).then((response) => setProduct(response.data)).catch(() => setMessage("Could not load wholesale product."));
  }, [businessSlug, productSlug]);

  async function onAdd() {
    const access = getStoredWholesaleAccess(businessSlug);
    if (!access || !product) return;
    const token = window.localStorage.getItem(cartKey(businessSlug));
    const cart = await getOrCreateCart(businessSlug, token);
    window.localStorage.setItem(cartKey(businessSlug), cart.data.session_token);
    await addCartItem(businessSlug, cart.data.session_token, {
      product_id: product.product_id,
      quantity: product.min_quantity_applied,
      wholesale_phone: access.phone,
      wholesale_token: access.token,
    });
    setMessage("Added wholesale quantity to cart.");
  }

  if (!product) return <p className="text-sm text-slate-600">{message ?? "Loading wholesale product..."}</p>;

  return (
    <section className="space-y-6">
      <div>
        <p className="text-sm font-medium uppercase tracking-wide text-teal-700">Wholesale</p>
        <h1 className="mt-2 text-3xl font-semibold">{product.name_en ?? product.name_ar}</h1>
        <p className="mt-3 text-xl font-semibold text-teal-700">{product.wholesale_price} {product.currency}</p>
        <p className="mt-2 text-sm text-slate-600">Minimum quantity: {product.min_quantity_applied}</p>
      </div>
      {message ? <p className="text-sm text-slate-600">{message}</p> : null}
      <button onClick={onAdd} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Add wholesale quantity to cart</button>
    </section>
  );
}

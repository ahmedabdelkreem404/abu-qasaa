"use client";

import { cancelOrder, getOrder, updateOrderStatus } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { Order, OrderStatus } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function OrderDetailPage() {
  const { id } = useParams<{ id: string }>();
  const [order, setOrder] = useState<Order | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { getOrder(id).then((r) => setOrder(r.data)).catch(() => setError("Could not load order.")); }, [id]);
  async function setStatus(status: OrderStatus) {
    const response = await updateOrderStatus(id, status, "Updated from dashboard.");
    setOrder(response.data);
  }
  async function onCancel() {
    const response = await cancelOrder(id, "Cancelled from dashboard.");
    setOrder(response.data);
  }
  if (error) return <ApiErrorState message={error} />;
  if (!order) return <div className="text-sm text-slate-600">Loading order...</div>;
  return <section className="space-y-6"><div className="flex flex-wrap items-center justify-between gap-3"><div><h1 className="text-2xl font-semibold">{order.order_number}</h1><p className="text-sm text-slate-600">{order.status} · {order.payment_status} · {order.fulfillment_status}</p></div><div className="flex gap-2"><select onChange={(e) => setStatus(e.target.value as OrderStatus)} defaultValue={order.status} className="rounded-md border border-slate-300 px-3 py-2 text-sm">{["pending_review", "pending_payment", "confirmed", "processing", "ready_to_ship", "shipped", "delivered", "cancelled"].map((s) => <option key={s} value={s}>{s}</option>)}</select><button onClick={onCancel} className="rounded-md border border-slate-300 px-3 py-2 text-sm">Cancel</button></div></div><div className="grid gap-4 md:grid-cols-3"><Box title="Customer" body={`${order.customer_name} / ${order.customer_phone}`} /><Box title="Total" body={`${order.grand_total} ${order.currency}`} /><Box title="Business Unit" body={order.business_unit?.slug ?? String(order.business_unit_id)} /></div><div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Items</h2><div className="mt-3 grid gap-2">{order.items?.map((item) => <p key={item.id} className="text-sm">{item.product_name_en ?? item.product_name_ar} - {item.quantity} x {item.unit_price}</p>)}</div></div><div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Status history</h2><div className="mt-3 grid gap-2">{order.status_histories?.map((item) => <p key={item.id} className="text-sm">{item.from_status ?? "-"} to {item.to_status}: {item.note ?? ""}</p>)}</div></div></section>;
}

function Box({ title, body }: { title: string; body: string }) {
  return <div className="rounded-md border border-slate-200 bg-white p-4"><h2 className="text-sm font-medium text-slate-500">{title}</h2><p className="mt-2 font-medium">{body}</p></div>;
}

"use client";

import { listOrders } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { Order } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function OrdersPage() {
  const [orders, setOrders] = useState<Order[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { listOrders().then((r) => setOrders(r.data)).catch((e) => setError(e instanceof Error && e.name === "403" ? "Forbidden." : "Could not load orders.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!orders) return <div className="text-sm text-slate-600">Loading orders...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Orders</h1><div className="rounded-md border border-slate-200 bg-white p-4"><div className="grid gap-3 md:grid-cols-5"><input placeholder="Business unit" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input placeholder="Status" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input placeholder="Payment" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input placeholder="Order number" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /><input placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2 text-sm" /></div></div>{orders.length === 0 ? <EmptyState message="No orders yet." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Order</th><th>Customer</th><th>Status</th><th>Total</th></tr></thead><tbody>{orders.map((order) => <tr key={order.id} className="border-t border-slate-100"><td className="p-3"><Link className="font-medium text-teal-700" href={`/dashboard/commerce/orders/${order.id}`}>{order.order_number}</Link></td><td>{order.customer_name}<br /><span className="text-slate-500">{order.customer_phone}</span></td><td>{order.status}</td><td>{order.grand_total} {order.currency}</td></tr>)}</tbody></table></div>}</section>;
}

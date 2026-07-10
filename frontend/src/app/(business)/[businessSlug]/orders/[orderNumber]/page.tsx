import { getPublicOrder } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";

export default async function PublicOrderPage({
  params,
  searchParams,
}: {
  params: Promise<{ businessSlug: string; orderNumber: string }>;
  searchParams: Promise<{ phone?: string }>;
}) {
  const { businessSlug, orderNumber } = await params;
  const { phone } = await searchParams;

  if (!phone) {
    return <form className="grid max-w-md gap-3 rounded-md border border-slate-200 bg-white p-5"><h1 className="text-2xl font-semibold">Track order</h1><input name="phone" placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2" /><button className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Track</button></form>;
  }

  const order = await getPublicOrder(businessSlug, orderNumber, phone).then((response) => response.data).catch(() => null);
  if (!order) return <ApiErrorState message="Order could not be found with this phone number." />;

  return <section className="space-y-6"><div><h1 className="text-3xl font-semibold">{order.order_number}</h1><p className="text-sm text-slate-600">{order.status} · {order.payment_status} · {order.fulfillment_status}</p></div><div className="rounded-md border border-slate-200 bg-white p-5"><p className="text-xl font-semibold">{order.grand_total} {order.currency}</p><p className="mt-2 text-sm text-slate-600">Your order is pending confirmation. No online payment has been collected.</p></div><div className="grid gap-3">{order.items?.map((item) => <div key={item.id} className="rounded-md border border-slate-200 bg-white p-4"><h2 className="font-medium">{item.product_name_en ?? item.product_name_ar}</h2><p className="text-sm text-slate-600">{item.quantity} x {item.unit_price}</p></div>)}</div></section>;
}

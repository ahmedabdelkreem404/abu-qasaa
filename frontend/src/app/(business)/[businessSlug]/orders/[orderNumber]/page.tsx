import { getPublicOrder, getPublicOrderPaymentOptions } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import Link from "next/link";

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
  const paymentOptions = await getPublicOrderPaymentOptions(businessSlug, orderNumber, phone).then((response) => response.data).catch(() => null);

  return <section className="space-y-6"><div><h1 className="text-3xl font-semibold">{order.order_number}</h1><p className="text-sm text-slate-600">{order.status} · {order.payment_status} · {order.fulfillment_status}</p></div><div className="rounded-md border border-slate-200 bg-white p-5"><p className="text-xl font-semibold">{order.grand_total} {order.currency}</p><p className="mt-2 text-sm text-slate-600">Manual payments are reviewed by an admin before the order is marked paid.</p>{order.payment_status !== "paid" ? <Link href={`/${businessSlug}/orders/${order.order_number}/payment?phone=${encodeURIComponent(phone)}`} className="mt-4 inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">View payment instructions</Link> : null}</div>{paymentOptions?.proofs.length ? <div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Payment proofs</h2><div className="mt-3 grid gap-2">{paymentOptions.proofs.map((proof) => <p key={proof.id} className="text-sm text-slate-700">{proof.payment_method?.name_en ?? proof.payment_method?.name_ar}: {proof.status}</p>)}</div></div> : null}<div className="grid gap-3">{order.items?.map((item) => <div key={item.id} className="rounded-md border border-slate-200 bg-white p-4"><h2 className="font-medium">{item.product_name_en ?? item.product_name_ar}</h2><p className="text-sm text-slate-600">{item.quantity} x {item.unit_price}</p></div>)}</div></section>;
}

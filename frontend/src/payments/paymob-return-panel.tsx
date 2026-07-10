"use client";

import { getPaymobReturnStatus, getPublicPaymentStatus } from "@/api/client";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";

export function PaymobReturnPanel() {
  const searchParams = useSearchParams();
  const [status, setStatus] = useState("processing");
  const business = searchParams.get("business") ?? searchParams.get("businessSlug") ?? "";
  const order = searchParams.get("order") ?? searchParams.get("orderNumber") ?? "";
  const phone = searchParams.get("phone") ?? "";

  useEffect(() => {
    const params = new URLSearchParams(searchParams.toString());
    getPaymobReturnStatus(params).then((response) => setStatus(response.data.status)).catch(() => setStatus("processing"));
    if (business && order && phone) {
      getPublicPaymentStatus(business, order, phone).then((response) => setStatus(response.data.order.payment_status)).catch(() => undefined);
    }
  }, [business, order, phone, searchParams]);

  return <section className="mx-auto grid max-w-xl gap-4 rounded-md border border-slate-200 bg-white p-6"><h1 className="text-2xl font-semibold">Payment is being confirmed</h1><p className="text-sm text-slate-600">We received your return from Paymob. The backend callback decides the final payment status.</p><p className="text-sm font-medium">Current status: {status}</p>{business && order && phone ? <Link className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white" href={`/${business}/orders/${order}?phone=${encodeURIComponent(phone)}`}>Check order status</Link> : null}</section>;
}

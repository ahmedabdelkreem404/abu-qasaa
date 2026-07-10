import { PaymobReturnPanel } from "@/payments/paymob-return-panel";
import { Suspense } from "react";

export default function PaymobReturnPage() {
  return <Suspense fallback={<div className="text-sm text-slate-600">Loading payment return...</div>}><PaymobReturnPanel /></Suspense>;
}

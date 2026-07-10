"use client";

import { listPaymobTransactions } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { PaymentTransaction } from "@/types/platform";
import { useEffect, useState } from "react";

export default function PaymobTransactionsPage() {
  const [transactions, setTransactions] = useState<PaymentTransaction[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => {
    let active = true;
    listPaymobTransactions().then((response) => { if (active) setTransactions(response.data); }).catch(() => { if (active) setError("Could not load Paymob transactions."); });
    return () => { active = false; };
  }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!transactions) return <div className="text-sm text-slate-600">Loading Paymob transactions...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Paymob transactions</h1>{transactions.length === 0 ? <EmptyState message="No Paymob transactions found." /> : <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Type</th><th>Status</th><th>Provider transaction</th><th>Provider status</th><th>Verified</th></tr></thead><tbody>{transactions.map((transaction) => <tr key={transaction.id} className="border-t border-slate-100"><td className="p-3">{transaction.type}</td><td>{transaction.status}</td><td>{transaction.provider_transaction_id ?? "-"}</td><td>{transaction.provider_status ?? "-"}</td><td>{transaction.verified_at ?? "-"}</td></tr>)}</tbody></table></div>}</section>;
}

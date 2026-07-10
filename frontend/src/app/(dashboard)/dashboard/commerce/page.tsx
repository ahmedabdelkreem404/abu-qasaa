import Link from "next/link";

export default function CommercePage() {
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Commerce</h1><div className="grid gap-4 md:grid-cols-2"><Link href="/dashboard/commerce/orders" className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Orders</h2><p className="mt-2 text-sm text-slate-600">Review and manage pending orders.</p></Link><Link href="/dashboard/commerce/customers" className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">Customers</h2><p className="mt-2 text-sm text-slate-600">View customer profiles and addresses.</p></Link></div></section>;
}

import { WholesaleAccessPanel, WholesaleApplicationForm } from "@/commerce/wholesale-tools";
import Link from "next/link";

export default async function WholesalePage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Wholesale</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Apply for wholesale access, check approval, and view partner prices after approval.</p>
      </div>
      <div className="flex flex-wrap gap-3">
        <Link href={`/${businessSlug}/wholesale/status`} className="rounded-md border border-teal-700 px-4 py-2 text-sm font-medium text-teal-800">Check status</Link>
        <Link href={`/${businessSlug}/wholesale/products`} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Wholesale products</Link>
      </div>
      <WholesaleAccessPanel businessSlug={businessSlug} />
      <WholesaleApplicationForm businessSlug={businessSlug} />
    </section>
  );
}

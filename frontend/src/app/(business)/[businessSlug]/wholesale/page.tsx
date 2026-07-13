import { WholesaleAccessPanel, WholesaleApplicationForm } from "@/commerce/wholesale-tools";
import Link from "next/link";

export default async function WholesalePage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-eyebrow">{businessSlug}</p>
        <h1 className="aq-title">Wholesale</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">Apply for wholesale access, check approval, and view partner prices after approval.</p>
      </div>
      <div className="flex flex-wrap gap-3">
        <Link href={`/${businessSlug}/wholesale/status`} className="aq-btn-secondary">Check status</Link>
        <Link href={`/${businessSlug}/wholesale/products`} className="aq-btn">Wholesale products</Link>
      </div>
      <WholesaleAccessPanel businessSlug={businessSlug} />
      <WholesaleApplicationForm businessSlug={businessSlug} />
    </section>
  );
}

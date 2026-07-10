import { WholesaleStatusLookup } from "@/commerce/wholesale-tools";

export default async function WholesaleStatusPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Wholesale status</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Look up the public-safe status for your application or approved wholesale profile.</p>
      </div>
      <WholesaleStatusLookup businessSlug={businessSlug} />
    </section>
  );
}

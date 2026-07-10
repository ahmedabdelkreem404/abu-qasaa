import { WholesaleAccessPanel, WholesaleProducts } from "@/commerce/wholesale-tools";

export default async function WholesaleProductsPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Wholesale products</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Approved wholesale customers can view assigned price-list pricing and minimum quantities.</p>
      </div>
      <WholesaleAccessPanel businessSlug={businessSlug} />
      <WholesaleProducts businessSlug={businessSlug} />
    </section>
  );
}

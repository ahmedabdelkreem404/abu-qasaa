import { listPublicSeasonalProducts } from "@/api/client";
import { ProductGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";

export default async function SeasonalPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const products = await listPublicSeasonalProducts(businessSlug).then((response) => response.data).catch(() => null);

  if (products === null) {
    return <ApiErrorState message="Seasonal products are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Seasonal</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Ramadan, Eid, and limited seasonal dates selections.</p>
      </div>
      <ProductGrid businessSlug={businessSlug} products={products} empty="No seasonal products are available." />
    </section>
  );
}

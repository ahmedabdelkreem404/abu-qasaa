import { getPublicCollection } from "@/api/client";
import { ProductGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";

export default async function CollectionDetailPage({ params }: { params: Promise<{ businessSlug: string; collectionSlug: string }> }) {
  const { businessSlug, collectionSlug } = await params;
  const collection = await getPublicCollection(businessSlug, collectionSlug).then((response) => response.data).catch(() => null);

  if (!collection) {
    return <ApiErrorState message="Collection is not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">{collection.name_en ?? collection.name_ar}</h1>
        <p className="mt-2 max-w-2xl text-slate-600">{collection.description_en ?? collection.description_ar ?? "Curated product selection."}</p>
      </div>
      <ProductGrid businessSlug={businessSlug} products={collection.products ?? []} empty="No products in this collection." />
    </section>
  );
}

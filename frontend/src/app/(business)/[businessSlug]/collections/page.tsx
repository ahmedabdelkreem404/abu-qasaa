import { listPublicCollections } from "@/api/client";
import { CollectionGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";

export default async function CollectionsPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const collections = await listPublicCollections(businessSlug).then((response) => response.data).catch(() => null);

  if (collections === null) {
    return <ApiErrorState message="Collections are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Collections</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Curated dates, gift boxes, and seasonal selections.</p>
      </div>
      <CollectionGrid businessSlug={businessSlug} collections={collections} />
    </section>
  );
}

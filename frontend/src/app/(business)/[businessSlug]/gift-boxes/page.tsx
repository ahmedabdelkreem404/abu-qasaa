import { listPublicGiftProducts } from "@/api/client";
import { ProductGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";

export default async function GiftBoxesPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const products = await listPublicGiftProducts(businessSlug).then((response) => response.data).catch(() => null);

  if (products === null) {
    return <ApiErrorState message="Gift boxes are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Gift Boxes</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Premium Ghosoun boxes for gifting and hospitality.</p>
      </div>
      <ProductGrid businessSlug={businessSlug} products={products} empty="No gift boxes are available." />
    </section>
  );
}

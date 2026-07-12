import { listPublicCorporateGiftProducts } from "@/api/client";
import { CorporateGiftForm } from "@/components/public/corporate-gift-form";
import { ProductGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";

export default async function CorporateGiftsPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const products = await listPublicCorporateGiftProducts(businessSlug).then((response) => response.data).catch(() => null);

  if (products === null) {
    return <ApiErrorState message="Corporate gifts are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Corporate Gifts</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Branded and bulk gifting options for companies.</p>
      </div>
      <ProductGrid businessSlug={businessSlug} products={products} empty="No corporate gift products are available." />
      <CorporateGiftForm businessSlug={businessSlug} />
    </section>
  );
}

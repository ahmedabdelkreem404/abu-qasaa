import { listPublicCorporateGiftProducts } from "@/api/client";
import { CorporateGiftForm } from "@/components/public/corporate-gift-form";
import { ProductGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";
import { getLocale } from "@/i18n/server";

export default async function CorporateGiftsPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const [products, locale] = await Promise.all([
    listPublicCorporateGiftProducts(businessSlug).then((response) => response.data).catch(() => null),
    getLocale(),
  ]);

  if (products === null) {
    return <ApiErrorState message="Corporate gifts are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-store-kicker">{businessSlug}</p>
        <h1 className="aq-store-title">{locale === "ar" ? "هدايا الشركات" : "Corporate gifts"}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{locale === "ar" ? "خيارات هدايا بالجملة وبهوية مخصصة للشركات." : "Bulk and branded gifting options for companies."}</p>
      </div>
      <ProductGrid businessSlug={businessSlug} products={products} empty={locale === "ar" ? "لا توجد منتجات هدايا شركات حاليا." : "No corporate gift products are available."} />
      <CorporateGiftForm businessSlug={businessSlug} />
    </section>
  );
}

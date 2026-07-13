import { listPublicGiftProducts } from "@/api/client";
import { ProductGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";
import { getLocale } from "@/i18n/server";

export default async function GiftBoxesPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const [products, locale] = await Promise.all([
    listPublicGiftProducts(businessSlug).then((response) => response.data).catch(() => null),
    getLocale(),
  ]);

  if (products === null) {
    return <ApiErrorState message="Gift boxes are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-store-kicker">{businessSlug}</p>
        <h1 className="aq-store-title">{locale === "ar" ? "علب الهدايا" : "Gift boxes"}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{locale === "ar" ? "علب راقية للهدايا والضيافة والمناسبات." : "Premium boxes for gifting, hospitality, and occasions."}</p>
      </div>
      <ProductGrid businessSlug={businessSlug} products={products} empty={locale === "ar" ? "لا توجد علب هدايا متاحة حاليا." : "No gift boxes are available."} />
    </section>
  );
}

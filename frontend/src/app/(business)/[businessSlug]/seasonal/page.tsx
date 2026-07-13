import { listPublicSeasonalProducts } from "@/api/client";
import { ProductGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";
import { getLocale } from "@/i18n/server";

export default async function SeasonalPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const [products, locale] = await Promise.all([
    listPublicSeasonalProducts(businessSlug).then((response) => response.data).catch(() => null),
    getLocale(),
  ]);

  if (products === null) {
    return <ApiErrorState message="Seasonal products are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-store-kicker">{businessSlug}</p>
        <h1 className="aq-store-title">{locale === "ar" ? "الموسمية" : "Seasonal"}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{locale === "ar" ? "اختيارات رمضان والعيد والمناسبات المحدودة." : "Ramadan, Eid, and limited seasonal selections."}</p>
      </div>
      <ProductGrid businessSlug={businessSlug} products={products} empty={locale === "ar" ? "لا توجد منتجات موسمية حاليا." : "No seasonal products are available."} />
    </section>
  );
}

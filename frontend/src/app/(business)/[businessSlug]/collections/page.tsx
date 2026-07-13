import { listPublicCollections } from "@/api/client";
import { CollectionGrid } from "@/components/public/merchandising";
import { ApiErrorState } from "@/components/shared/api-state";
import { getLocale } from "@/i18n/server";

export default async function CollectionsPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const [collections, locale] = await Promise.all([
    listPublicCollections(businessSlug).then((response) => response.data).catch(() => null),
    getLocale(),
  ]);

  if (collections === null) {
    return <ApiErrorState message="Collections are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-store-kicker">{businessSlug}</p>
        <h1 className="aq-store-title">{locale === "ar" ? "المجموعات" : "Collections"}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{locale === "ar" ? "اختيارات منتقاة من المنتجات والهدايا والموسميات." : "Curated products, gift boxes, and seasonal selections."}</p>
      </div>
      <CollectionGrid businessSlug={businessSlug} collections={collections} />
    </section>
  );
}

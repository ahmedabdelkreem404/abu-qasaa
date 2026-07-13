import { getPublicBusinessUnitBySlug } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import { getLocale } from "@/i18n/server";
import { StorefrontGallery } from "@/storefront/components";
import { getStorefrontProfile, storefrontName } from "@/storefront/profiles";

export default async function BusinessGalleryPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const [{ businessSlug }, locale] = await Promise.all([params, getLocale()]);
  const unit = await getPublicBusinessUnitBySlug(businessSlug).then((response) => response.data).catch(() => null);

  if (!unit) {
    return <ApiErrorState message={locale === "ar" ? "النشاط غير متاح حاليا." : "Business unit is not available."} />;
  }

  const profile = getStorefrontProfile(businessSlug);
  const name = storefrontName(unit, locale);

  return (
    <section className="aq-store-section">
      <div className="aq-store-section-head">
        <div>
          <p className="aq-store-kicker">{name}</p>
          <h1 className="aq-store-title">{locale === "ar" ? "المعرض" : "Gallery"}</h1>
          <p className="aq-subtitle mt-2 max-w-2xl">{profile.promise[locale]}</p>
        </div>
      </div>
      <StorefrontGallery images={profile.gallery} title={name} />
    </section>
  );
}

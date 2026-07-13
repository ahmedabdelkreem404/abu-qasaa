import { getBusinessUnitPublicCmsPage, getPublicBusinessUnitBySlug, listPublicCollections, listPublicProducts } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState } from "@/components/shared/api-state";
import { getDictionary, getLocale } from "@/i18n/server";
import type { BusinessUnit, CmsPage } from "@/types/platform";
import { StorefrontGallery, StorefrontProductGrid } from "@/storefront/components";
import { getStorefrontProfile, storefrontName } from "@/storefront/profiles";
import Image from "next/image";
import Link from "next/link";

async function loadBusinessUnit(slug: string): Promise<BusinessUnit | null> {
  try {
    const response = await getPublicBusinessUnitBySlug(slug);
    return response.data;
  } catch {
    return null;
  }
}

export default async function BusinessHomePage({
  params,
}: {
  params: Promise<{ businessSlug: string }>;
}) {
  const { businessSlug } = await params;
  const [locale, dictionary] = await Promise.all([getLocale(), getDictionary()]);
  const unit = await loadBusinessUnit(businessSlug);
  const comingSoon = dictionary.businessUnits.comingSoon;

  if (unit === null) {
    return <ApiErrorState message={dictionary.common.noData} />;
  }

  const page: CmsPage | null = await getBusinessUnitPublicCmsPage(businessSlug)
    .then((response) => response.data)
    .catch(() => null);
  const hasProducts = unit.modules?.some((module) => module.key === "products" && module.is_enabled) ?? false;
  const hasWholesale = (unit.modules?.some((module) => module.key === "wholesale" && module.is_enabled) ?? false)
    && (unit.settings?.some((setting) => setting.key === "wholesale_enabled" && Boolean(setting.value)) ?? unit.type === "wholesale_store");
  const profile = getStorefrontProfile(unit.slug);
  const name = storefrontName(unit, locale);
  const featuredProducts = hasProducts
    ? await listPublicProducts(businessSlug, new URLSearchParams({ is_featured: "true", per_page: "6" })).then((response) => response.data).catch(() => [])
    : [];
  const visibleFeaturedProducts = locale === "ar"
    ? featuredProducts.filter((product) => hasArabicText(product.name_ar))
    : featuredProducts;
  const collections = hasProducts ? await listPublicCollections(businessSlug).then((response) => response.data.slice(0, 3)).catch(() => []) : [];

  if (page) {
    return (
      <section className="space-y-12">
        <div className="aq-store-hero">
          <div className="aq-store-hero-media">
            <Image src={profile.heroImage} alt={name} fill priority sizes="100vw" />
          </div>
          <div className="aq-store-hero-content">
            <p className="aq-store-kicker">{dictionary.businessUnits.types[unit.slug as keyof typeof dictionary.businessUnits.types] ?? unit.type}</p>
            <h1 className="aq-store-display">{name}</h1>
            <p className="aq-store-copy">{profile.promise[locale]}</p>
            <div className="flex flex-wrap gap-3">
              {hasProducts ? <Link href={`/${businessSlug}/products`} className="aq-btn aq-btn-light">{dictionary.common.browseProducts}</Link> : null}
              <Link href={`/${businessSlug}/gallery`} className="aq-btn-secondary aq-btn-dark">{locale === "ar" ? "شاهد المعرض" : "View gallery"}</Link>
              {hasWholesale ? <Link href={`/${businessSlug}/wholesale`} className="aq-btn-secondary aq-btn-dark">{dictionary.businessUnits.wholesalePartner}</Link> : null}
            </div>
          </div>
        </div>
        <SectionRenderer sections={page.sections} locale={locale} />
        {hasProducts ? <MerchLinks businessSlug={businessSlug} labels={dictionary.businessUnits.merchLinks} /> : null}
        <div className="aq-store-section">
          <div className="aq-store-section-head">
            <div>
              <p className="aq-store-kicker">{locale === "ar" ? "هوية بصرية" : "Visual identity"}</p>
              <h2 className="aq-store-title">{locale === "ar" ? "من عالم النشاط" : "From this brand world"}</h2>
            </div>
            <Link href={`/${businessSlug}/gallery`} className="aq-store-pill">{locale === "ar" ? "كل الصور" : "All images"}</Link>
          </div>
          <StorefrontGallery images={profile.gallery.slice(0, 4)} title={name} />
        </div>
        {visibleFeaturedProducts.length > 0 ? (
          <div className="aq-store-section">
            <div className="aq-store-section-head">
              <div>
                <p className="aq-store-kicker">{dictionary.businessUnits.featuredProducts}</p>
                <h2 className="aq-store-title">{locale === "ar" ? "مختارات النشاط" : "Featured selections"}</h2>
              </div>
              <Link href={`/${businessSlug}/products`} className="aq-store-pill">{dictionary.common.viewDetails}</Link>
            </div>
            <StorefrontProductGrid businessSlug={businessSlug} products={visibleFeaturedProducts} empty={dictionary.common.noData} locale={locale} profile={profile} />
          </div>
        ) : null}
      </section>
    );
  }

  return (
    <section className="space-y-6">
      <div className="space-y-2">
        <p className="aq-eyebrow">
          {dictionary.businessUnits.types[unit.slug as keyof typeof dictionary.businessUnits.types] ?? unit.type}
        </p>
        <h1 className="aq-title">{locale === "ar" ? unit.name_ar : (unit.name_en ?? unit.name_ar)}</h1>
        <p className="aq-subtitle max-w-2xl">
          {dictionary.businessUnits.descriptions[unit.slug as keyof typeof dictionary.businessUnits.descriptions] ?? unit.description ?? comingSoon[unit.type as keyof typeof comingSoon] ?? dictionary.common.noData}
        </p>
        {hasWholesale ? <a href={`/${businessSlug}/wholesale`} className="aq-btn mt-4">{dictionary.businessUnits.wholesalePartner}</a> : null}
      </div>
      {hasProducts ? <MerchLinks businessSlug={businessSlug} labels={dictionary.businessUnits.merchLinks} /> : <div className="aq-card p-6">{comingSoon[unit.type as keyof typeof comingSoon] ?? dictionary.common.noData}</div>}
      {collections.length > 0 ? <div className="aq-grid-auto">{collections.map((collection) => <a key={collection.id} href={`/${businessSlug}/collections/${collection.slug}`} className="aq-card p-5"><h2 className="font-black">{locale === "ar" ? collection.name_ar : (collection.name_en ?? collection.name_ar)}</h2><p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{locale === "ar" ? (collection.description_ar ?? collection.description_en ?? dictionary.businessUnits.curatedSelection) : (collection.description_en ?? collection.description_ar ?? dictionary.businessUnits.curatedSelection)}</p></a>)}</div> : null}
    </section>
  );
}

function MerchLinks({ businessSlug, labels }: { businessSlug: string; labels: Record<string, string> }) {
  return (
    <div className="flex flex-wrap gap-2">
      {[
        [labels.collections, `/${businessSlug}/collections`],
        [labels.giftBoxes, `/${businessSlug}/gift-boxes`],
        [labels.corporateGifts, `/${businessSlug}/corporate-gifts`],
        [labels.seasonal, `/${businessSlug}/seasonal`],
      ].map(([label, href]) => <a key={href} href={href} className="aq-btn-secondary">{label}</a>)}
    </div>
  );
}

function hasArabicText(value?: string | null) {
  return Boolean(value && /[\u0600-\u06FF]/.test(value));
}

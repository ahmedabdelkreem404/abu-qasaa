import { getPublicBusinessUnitBySlug } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import { getLocale } from "@/i18n/server";
import { getStorefrontProfile, storefrontName } from "@/storefront/profiles";

export default async function BusinessPoliciesPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const [{ businessSlug }, locale] = await Promise.all([params, getLocale()]);
  const unit = await getPublicBusinessUnitBySlug(businessSlug).then((response) => response.data).catch(() => null);

  if (!unit) {
    return <ApiErrorState message={locale === "ar" ? "النشاط غير متاح حاليا." : "Business unit is not available."} />;
  }

  const profile = getStorefrontProfile(businessSlug);

  return (
    <section className="aq-store-section">
      <div>
        <p className="aq-store-kicker">{storefrontName(unit, locale)}</p>
        <h1 className="aq-store-title">{locale === "ar" ? "السياسات والتفاصيل" : "Policies and details"}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{profile.promise[locale]}</p>
      </div>
      <div className="aq-store-policy-grid">
        {profile.policies.map((policy) => (
          <article key={policy.titleEn} className="aq-store-policy">
            <h2 className="text-xl font-black">{locale === "ar" ? policy.titleAr : policy.titleEn}</h2>
            <p className="mt-3 text-sm leading-8 text-[var(--aq-muted)]">{locale === "ar" ? policy.bodyAr : policy.bodyEn}</p>
          </article>
        ))}
      </div>
    </section>
  );
}

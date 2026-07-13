import Link from "next/link";
import { getPublicCmsPageBySlug, listPublicBusinessUnits } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import { getDictionary, getLocale } from "@/i18n/server";
import type { BusinessUnit, CmsSection } from "@/types/platform";

async function loadBusinessUnits(): Promise<BusinessUnit[] | null> {
  try {
    const response = await listPublicBusinessUnits();
    return response.data;
  } catch {
    return null;
  }
}

export default async function BusinessUnitsPage() {
  const [businessUnits, cmsPage, dictionary, locale] = await Promise.all([
    loadBusinessUnits(),
    getPublicCmsPageBySlug("business-units").then((response) => response.data).catch(() => null),
    getDictionary(),
    getLocale(),
  ]);
  const sections: CmsSection[] = cmsPage?.sections ?? [];
  const unitCopy = dictionary.businessUnits;

  if (businessUnits === null) {
    return <ApiErrorState message="Business units could not be loaded from the API." />;
  }

  return (
    <section className="space-y-8">
      <div className="aq-hero">
        <div className="aq-hero-layout">
          <div className="aq-measure">
            <p className="text-sm font-black text-[var(--aq-gold)]">{dictionary.home.eyebrow}</p>
            <h1 className="aq-display mt-3 text-white">{dictionary.public.businessUnitsTitle}</h1>
            <p className="mt-5 max-w-2xl text-base leading-8 text-white/78">{dictionary.public.businessUnitsBody}</p>
          </div>
          <div className="aq-logo-panel">
            <div>
              <p className="text-5xl font-black text-white">4</p>
              <p className="aq-logo-caption">{dictionary.nav.businessUnits}</p>
            </div>
          </div>
        </div>
      </div>
      {sections.length > 0 ? <SectionRenderer sections={sections.filter((section) => section.section_type !== "hero")} locale={locale} /> : null}
      {businessUnits.length === 0 ? (
        <EmptyState message={dictionary.common.noData} />
      ) : (
        <div className="aq-grid-auto">
          {businessUnits.map((unit) => (
            <Link key={unit.id} href={`/${unit.slug}`} className={`aq-business-card group ${unitClassName(unit.slug)}`}>
              <span className="aq-chip">{unitCopy.types[unit.slug as keyof typeof unitCopy.types] ?? unit.type}</span>
              <h2 className="mt-4 text-xl font-black">{locale === "ar" ? unit.name_ar : (unit.name_en ?? unit.name_ar)}</h2>
              <p className="mt-3 text-sm leading-7 text-[var(--aq-muted)]">
                {unitCopy.descriptions[unit.slug as keyof typeof unitCopy.descriptions] ?? unit.description ?? dictionary.common.learnMore}
              </p>
              <p className="mt-5 text-sm font-black text-[var(--aq-primary-2)]">{dictionary.common.viewDetails}</p>
            </Link>
          ))}
        </div>
      )}
    </section>
  );
}

function unitClassName(slug: string) {
  if (slug === "oils") return "aq-unit-oils";
  if (slug === "dates") return "aq-unit-dates";
  if (slug === "real-estate") return "aq-unit-real-estate";
  if (slug === "import-export") return "aq-unit-import-export";
  return "";
}

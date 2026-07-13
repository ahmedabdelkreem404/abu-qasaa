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

  if (businessUnits === null) {
    return <ApiErrorState message="Business units could not be loaded from the API." />;
  }

  return (
    <section className="space-y-6">
      {sections.length > 0 ? <SectionRenderer sections={sections} /> : <div>
        <p className="aq-eyebrow">{dictionary.home.eyebrow}</p>
        <h1 className="aq-title">{dictionary.public.businessUnitsTitle}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">
          {dictionary.public.businessUnitsBody}
        </p>
      </div>}
      {businessUnits.length === 0 ? (
        <EmptyState message="No active business units are published yet." />
      ) : (
        <div className="aq-grid-auto">
          {businessUnits.map((unit) => (
            <Link key={unit.id} href={`/${unit.slug}`} className="aq-card group p-5 transition hover:-translate-y-1 hover:border-[color:var(--aq-primary)]">
              <span className="aq-chip">{unit.type}</span>
              <h2 className="mt-4 text-xl font-black">{locale === "ar" ? unit.name_ar : (unit.name_en ?? unit.name_ar)}</h2>
              <p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{unit.description ?? dictionary.common.learnMore}</p>
            </Link>
          ))}
        </div>
      )}
    </section>
  );
}

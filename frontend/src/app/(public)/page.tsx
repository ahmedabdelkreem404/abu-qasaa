import Link from "next/link";
import Image from "next/image";
import { getPublicCmsPageBySlug, listPublicBusinessUnits } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState } from "@/components/shared/api-state";
import { getDictionary, getLocale } from "@/i18n/server";
import type { CmsPage } from "@/types/platform";

export default async function HomePage() {
  const [dictionary, locale] = await Promise.all([getDictionary(), getLocale()]);
  let page: CmsPage | null = null;
  const businessUnits = await listPublicBusinessUnits().then((response) => response.data).catch(() => []);

  try {
    page = (await getPublicCmsPageBySlug("home")).data;
  } catch {
    page = null;
  }

  const sectors = [
    { label: dictionary.sectors.oils, href: "/oils", tone: dictionary.home.sectorTags.oils, metric: dictionary.home.sectorMetrics.oils, className: "aq-unit-oils" },
    { label: dictionary.sectors.dates, href: "/dates", tone: dictionary.home.sectorTags.dates, metric: dictionary.home.sectorMetrics.dates, className: "aq-unit-dates" },
    { label: dictionary.sectors.realEstate, href: "/real-estate", tone: dictionary.home.sectorTags.realEstate, metric: dictionary.home.sectorMetrics.realEstate, className: "aq-unit-real-estate" },
    { label: dictionary.sectors.importExport, href: "/import-export", tone: dictionary.home.sectorTags.importExport, metric: dictionary.home.sectorMetrics.importExport, className: "aq-unit-import-export" },
  ];

  return (
    <section className="space-y-12">
      <div className="aq-hero">
        <div className="aq-hero-layout">
        <div className="aq-measure">
          <p className="text-sm font-black text-[var(--aq-gold)]">{dictionary.home.eyebrow}</p>
          <h1 className="aq-display mt-4 text-white">
            {dictionary.home.title}
          </h1>
          <p className="mt-5 max-w-2xl text-base leading-8 text-white/78">{dictionary.home.body}</p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link href="/business-units" className="aq-btn aq-btn-light">
              {dictionary.home.primaryCta}
            </Link>
            <Link href="/dashboard" className="aq-btn-secondary aq-btn-dark">
              {dictionary.home.secondaryCta}
            </Link>
          </div>
        </div>
        <div className="aq-logo-panel">
          <div>
            <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={224} height={224} priority className="aq-logo-hero" />
            <p className="aq-logo-caption">{dictionary.common.oils}</p>
          </div>
        </div>
        </div>
      </div>

      <div className="aq-stat-grid">
        {[
          ["4", dictionary.nav.businessUnits],
          ["52", dictionary.home.permissionsMetric],
          ["2027", dictionary.home.experienceMetric],
        ].map(([value, label]) => (
          <div key={label} className="aq-stat-card">
            <p className="text-3xl font-black text-[var(--aq-primary)]">{value}</p>
            <p className="mt-1 text-sm font-bold text-[var(--aq-muted)]">{label}</p>
          </div>
        ))}
      </div>

      {page ? <SectionRenderer sections={page.sections?.filter((section) => section.section_type !== "hero")} locale={locale} /> : (
        <ApiErrorState message={dictionary.common.noData} />
      )}

      <div className="space-y-5">
        <div>
          <p className="aq-eyebrow">{dictionary.home.valuesTitle}</p>
          <h2 className="aq-title">{dictionary.home.sectorsTitle}</h2>
        </div>
        <div className="aq-grid-auto">
          {sectors.map((sector) => (
            <Link key={sector.href} href={sector.href} className={`aq-business-card group ${sector.className}`}>
              <span className="aq-chip">{sector.tone}</span>
              <h3 className="mt-5 text-xl font-black text-[var(--aq-ink)]">{sector.label}</h3>
              <p className="mt-3 text-sm font-bold text-[var(--aq-muted)]">{sector.metric}</p>
            </Link>
          ))}
        </div>
      </div>

      {businessUnits.length > 0 ? (
        <div className="aq-card p-6">
          <div className="flex flex-wrap items-end justify-between gap-4">
            <div>
              <p className="aq-eyebrow">{dictionary.nav.businessUnits}</p>
              <h2 className="text-2xl font-black">{dictionary.public.businessUnitsBody}</h2>
            </div>
            <Link href="/business-units" className="aq-btn-secondary">{dictionary.common.viewDetails}</Link>
          </div>
          <div className="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            {businessUnits.map((unit) => (
              <Link key={unit.id} href={`/${unit.slug}`} className="rounded-md border border-[color:var(--aq-line)] bg-[var(--aq-surface-2)] p-4 transition hover:bg-white">
                <p className="font-black">{locale === "ar" ? unit.name_ar : (unit.name_en ?? unit.name_ar)}</p>
                <p className="mt-2 text-xs font-bold text-[var(--aq-muted)]">
                  {dictionary.businessUnits.types[unit.slug as keyof typeof dictionary.businessUnits.types] ?? unit.type}
                </p>
              </Link>
            ))}
          </div>
        </div>
      ) : null}
    </section>
  );
}

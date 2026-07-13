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
    { label: dictionary.sectors.oils, href: "/oils", tone: "Industrial", metric: "B2B + B2C" },
    { label: dictionary.sectors.dates, href: "/dates", tone: "Premium", metric: "Gifts" },
    { label: dictionary.sectors.realEstate, href: "/real-estate", tone: "Property", metric: "Leads" },
    { label: dictionary.sectors.importExport, href: "/import-export", tone: "Services", metric: "RFQ" },
  ];

  return (
    <section className="space-y-12">
      <div className="aq-hero grid gap-8 px-5 py-10 sm:px-8 lg:grid-cols-[1fr_360px] lg:px-10 lg:py-16">
        <div>
          <p className="text-sm font-black text-[var(--aq-gold)]">{dictionary.home.eyebrow}</p>
          <h1 className="mt-4 max-w-5xl text-4xl font-black leading-tight text-white sm:text-6xl lg:text-7xl">
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
        <div className="flex items-center justify-center">
          <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={288} height={288} priority className="h-56 w-56 rounded-md bg-white object-contain p-4 shadow-2xl sm:h-72 sm:w-72" />
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        {[
          ["4", dictionary.nav.businessUnits],
          ["52", "Permissions"],
          ["2027", "Experience standard"],
        ].map(([value, label]) => (
          <div key={label} className="aq-card p-5">
            <p className="text-3xl font-black text-[var(--aq-primary)]">{value}</p>
            <p className="mt-1 text-sm font-bold text-[var(--aq-muted)]">{label}</p>
          </div>
        ))}
      </div>

      {page ? <SectionRenderer sections={page.sections?.filter((section) => section.section_type !== "hero")} /> : (
        <ApiErrorState message="Home content is not available right now." />
      )}

      <div className="space-y-5">
        <div>
          <p className="aq-eyebrow">{dictionary.home.valuesTitle}</p>
          <h2 className="aq-title">{dictionary.home.sectorsTitle}</h2>
        </div>
        <div className="aq-grid-auto">
          {sectors.map((sector) => (
            <Link key={sector.href} href={sector.href} className="aq-card group p-5 transition hover:-translate-y-1 hover:border-[color:var(--aq-primary)]">
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
                <p className="mt-2 text-xs font-bold text-[var(--aq-muted)]">{unit.type}</p>
              </Link>
            ))}
          </div>
        </div>
      ) : null}
    </section>
  );
}

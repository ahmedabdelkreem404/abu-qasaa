import Link from "next/link";
import Image from "next/image";
import type { CmsSection } from "@/types/platform";

export function SectionRenderer({ sections = [] }: { sections?: CmsSection[] }) {
  return (
    <div className="space-y-10">
      {sections.map((section, index) => (
        <CmsSectionBlock key={`${section.section_type}-${section.id ?? index}`} section={section} />
      ))}
    </div>
  );
}

function CmsSectionBlock({ section }: { section: CmsSection }) {
  const title = section.title_en ?? section.title_ar;
  const subtitle = section.subtitle_en ?? section.subtitle_ar;
  const body = section.body_en ?? section.body_ar;

  if (section.section_type === "hero") {
    return (
      <section className="aq-hero grid gap-8 px-5 py-10 sm:px-8 lg:grid-cols-[1fr_340px] lg:px-10 lg:py-16">
        <div>
          {subtitle ? <p className="text-sm font-black text-[var(--aq-gold)]">{subtitle}</p> : null}
          <h1 className="mt-3 max-w-4xl text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">{title}</h1>
          <p className="mt-5 max-w-2xl text-base leading-8 text-white/78">{body}</p>
          {section.button_url ? (
            <Link href={section.button_url} className="aq-btn aq-btn-light mt-7">
              {section.button_label_en ?? section.button_label_ar ?? "Learn more"}
            </Link>
          ) : null}
        </div>
        <div className="hidden items-center justify-center lg:flex">
          <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={256} height={256} className="h-64 w-64 rounded-md bg-white object-contain p-4 shadow-2xl" />
        </div>
      </section>
    );
  }

  if (section.section_type === "cards" || section.section_type === "stats") {
    const items = Array.isArray(section.data_json?.items) ? section.data_json.items : [];
    return (
      <section className="space-y-4">
        <div>
          <p className="aq-eyebrow">{subtitle}</p>
          <h2 className="aq-title">{title}</h2>
        </div>
        <div className="aq-grid-auto">
          {items.map((item, index) => (
            <div key={index} className="aq-card p-5 text-sm">
              {formatSectionItem(item)}
            </div>
          ))}
        </div>
      </section>
    );
  }

  if (section.section_type === "contact_cta") {
    return (
      <section className="aq-hero grid gap-5 p-6 md:grid-cols-[1fr_auto] md:items-center">
        <div>
          <h2 className="text-2xl font-black">{title}</h2>
          <p className="mt-2 text-white/75">{body}</p>
        </div>
        <Link href={section.button_url ?? "/contact"} className="aq-btn aq-btn-light">
          {section.button_label_en ?? "Contact us"}
        </Link>
      </section>
    );
  }

  return (
    <section className="aq-card p-6">
      {subtitle ? <p className="aq-eyebrow">{subtitle}</p> : null}
      <h2 className="text-2xl font-black text-[var(--aq-ink)]">{title}</h2>
      {body ? <p className="mt-3 whitespace-pre-line text-[var(--aq-muted)] leading-8">{body}</p> : null}
    </section>
  );
}

function formatSectionItem(item: unknown) {
  if (typeof item !== "object" || item === null) {
    return String(item);
  }

  const record = item as Record<string, unknown>;
  const title = record.title_en ?? record.title_ar ?? record.label_en ?? record.label_ar ?? record.value;
  const body = record.body_en ?? record.body_ar ?? record.description_en ?? record.description_ar;

  return (
    <div className="space-y-1">
      {title ? <p className="font-black text-[var(--aq-ink)]">{String(title)}</p> : null}
      {body ? <p className="leading-7 text-[var(--aq-muted)]">{String(body)}</p> : null}
    </div>
  );
}

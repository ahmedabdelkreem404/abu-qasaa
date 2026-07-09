import Link from "next/link";
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
      <section className="rounded-md bg-white px-6 py-12 shadow-sm">
        <p className="text-sm font-medium uppercase tracking-wide text-teal-700">{subtitle}</p>
        <h1 className="mt-2 max-w-3xl text-4xl font-semibold text-slate-950">{title}</h1>
        <p className="mt-4 max-w-2xl text-slate-600">{body}</p>
        {section.button_url ? (
          <Link href={section.button_url} className="mt-6 inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">
            {section.button_label_en ?? section.button_label_ar ?? "Learn more"}
          </Link>
        ) : null}
      </section>
    );
  }

  if (section.section_type === "cards" || section.section_type === "stats") {
    const items = Array.isArray(section.data_json?.items) ? section.data_json.items : [];
    return (
      <section className="space-y-4">
        <h2 className="text-2xl font-semibold">{title}</h2>
        <div className="grid gap-4 md:grid-cols-3">
          {items.map((item, index) => (
            <div key={index} className="rounded-md border border-slate-200 bg-white p-5 text-sm">
              {formatSectionItem(item)}
            </div>
          ))}
        </div>
      </section>
    );
  }

  if (section.section_type === "contact_cta") {
    return (
      <section className="rounded-md border border-teal-100 bg-teal-50 p-6">
        <h2 className="text-2xl font-semibold">{title}</h2>
        <p className="mt-2 text-slate-700">{body}</p>
        <Link href={section.button_url ?? "/contact"} className="mt-4 inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">
          {section.button_label_en ?? "Contact us"}
        </Link>
      </section>
    );
  }

  return (
    <section className="rounded-md border border-slate-200 bg-white p-6">
      <h2 className="text-2xl font-semibold">{title}</h2>
      {subtitle ? <p className="mt-1 text-slate-500">{subtitle}</p> : null}
      {body ? <p className="mt-3 whitespace-pre-line text-slate-700">{body}</p> : null}
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
      {title ? <p className="font-medium text-slate-950">{String(title)}</p> : null}
      {body ? <p className="text-slate-600">{String(body)}</p> : null}
    </div>
  );
}

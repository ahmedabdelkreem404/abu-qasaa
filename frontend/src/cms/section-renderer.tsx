import Link from "next/link";
import Image from "next/image";
import type { CmsSection } from "@/types/platform";
import type { Locale } from "@/i18n";

export function SectionRenderer({ sections = [], locale = "ar" }: { sections?: CmsSection[]; locale?: Locale }) {
  return (
    <div className="space-y-10">
      {sections.map((section, index) => (
        <CmsSectionBlock key={`${section.section_type}-${section.id ?? index}`} section={section} locale={locale} />
      ))}
    </div>
  );
}

function localized(locale: Locale, ar?: string | null, en?: string | null) {
  return locale === "ar" ? ar : (en ?? ar);
}

function CmsSectionBlock({ section, locale }: { section: CmsSection; locale: Locale }) {
  const title = localized(locale, section.title_ar, section.title_en);
  const subtitle = localized(locale, section.subtitle_ar, section.subtitle_en);
  const body = localized(locale, section.body_ar, section.body_en);
  const buttonLabel = localized(locale, section.button_label_ar, section.button_label_en);

  if (!title && !subtitle && !body) {
    return null;
  }

  if (section.section_type === "hero") {
    return (
      <section className="aq-hero">
        <div className="aq-hero-layout">
        <div className="aq-measure">
          {subtitle ? <p className="text-sm font-black text-[var(--aq-gold)]">{subtitle}</p> : null}
          {title ? <h1 className="aq-display mt-3 text-white">{title}</h1> : null}
          {body ? <p className="mt-5 max-w-2xl text-base leading-8 text-white/78">{body}</p> : null}
          {section.button_url ? (
            <Link href={section.button_url} className="aq-btn aq-btn-light mt-7">
              {buttonLabel ?? (locale === "ar" ? "اعرف المزيد" : "Learn more")}
            </Link>
          ) : null}
        </div>
        <div className="aq-logo-panel">
          <div>
            <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={208} height={208} className="aq-logo-hero" />
            <p className="aq-logo-caption">{locale === "ar" ? "أبو قصعة للزيوت" : "ABU QASAA OILS"}</p>
          </div>
        </div>
        </div>
      </section>
    );
  }

  if (section.section_type === "cards" || section.section_type === "stats") {
    const items = Array.isArray(section.data_json?.items) ? section.data_json.items : [];
    return (
      <section className="space-y-4">
        <div>
          {subtitle ? <p className="aq-eyebrow">{subtitle}</p> : null}
          {title ? <h2 className="aq-title">{title}</h2> : null}
        </div>
        <div className="aq-grid-auto">
          {items.map((item, index) => (
            <div key={index} className="aq-card p-5 text-sm">
              {formatSectionItem(item, locale)}
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
          {title ? <h2 className="text-2xl font-black">{title}</h2> : null}
          {body ? <p className="mt-2 text-white/75">{body}</p> : null}
        </div>
        <Link href={section.button_url ?? "/contact"} className="aq-btn aq-btn-light">
          {buttonLabel ?? (locale === "ar" ? "تواصل معنا" : "Contact us")}
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

function formatSectionItem(item: unknown, locale: Locale) {
  if (typeof item !== "object" || item === null) {
    return String(item);
  }

  const record = item as Record<string, unknown>;
  const title = localized(locale, stringValue(record.title_ar ?? record.label_ar), stringValue(record.title_en ?? record.label_en)) ?? record.value;
  const body = localized(locale, stringValue(record.body_ar ?? record.description_ar), stringValue(record.body_en ?? record.description_en));

  if (!title && !body) {
    return null;
  }

  return (
    <div className="space-y-1">
      {title ? <p className="font-black text-[var(--aq-ink)]">{String(title)}</p> : null}
      {body ? <p className="leading-7 text-[var(--aq-muted)]">{String(body)}</p> : null}
    </div>
  );
}

function stringValue(value: unknown) {
  return typeof value === "string" ? value : null;
}

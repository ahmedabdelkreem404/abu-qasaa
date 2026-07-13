import { getPublicCmsPageBySlug } from "@/api/client";
import { ContactForm } from "@/cms/contact-form";
import { SectionRenderer } from "@/cms/section-renderer";
import { getDictionary, getLocale } from "@/i18n/server";
import type { CmsSection } from "@/types/platform";

export default async function ContactPage() {
  const [dictionary, locale] = await Promise.all([getDictionary(), getLocale()]);
  let sections: CmsSection[] = [];

  try {
    const page = await getPublicCmsPageBySlug("contact");
    sections = page.data.sections ?? [];
  } catch {
    sections = [];
  }

  return (
    <section className="space-y-8">
      {sections.length > 0 ? <SectionRenderer sections={sections} locale={locale} /> : (
        <div className="aq-card p-6">
          <p className="aq-eyebrow">{dictionary.home.contactTitle}</p>
          <h1 className="aq-title">{dictionary.public.contactTitle}</h1>
          <p className="aq-subtitle mt-2">{dictionary.home.contactBody}</p>
        </div>
      )}
      <ContactForm />
    </section>
  );
}

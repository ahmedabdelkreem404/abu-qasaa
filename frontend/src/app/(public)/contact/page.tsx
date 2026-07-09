import { getPublicCmsPageBySlug } from "@/api/client";
import { ContactForm } from "@/cms/contact-form";
import { SectionRenderer } from "@/cms/section-renderer";
import type { CmsSection } from "@/types/platform";

export default async function ContactPage() {
  let sections: CmsSection[] = [];

  try {
    const page = await getPublicCmsPageBySlug("contact");
    sections = page.data.sections ?? [];
  } catch {
    sections = [];
  }

  return (
    <section className="space-y-8">
      {sections.length > 0 ? <SectionRenderer sections={sections} /> : <h1 className="text-3xl font-semibold">Contact</h1>}
      <ContactForm />
    </section>
  );
}

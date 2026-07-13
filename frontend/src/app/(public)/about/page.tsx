import { getPublicCmsPageBySlug } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState } from "@/components/shared/api-state";
import { getLocale } from "@/i18n/server";
import type { CmsPage } from "@/types/platform";

export default async function AboutPage() {
  const locale = await getLocale();
  let page: CmsPage | null = null;

  try {
    page = (await getPublicCmsPageBySlug("about")).data;
  } catch {
    return <ApiErrorState message="About content is not available right now." />;
  }

  return (
    <section className="space-y-6">
      <div className="aq-card p-6">
        <p className="aq-eyebrow">Abu Qasaa</p>
        <h1 className="aq-title">{locale === "ar" ? page.title_ar : (page.title_en ?? page.title_ar)}</h1>
      </div>
      <SectionRenderer sections={page.sections} />
    </section>
  );
}

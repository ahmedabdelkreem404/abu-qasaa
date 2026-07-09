import { getPublicCmsPageBySlug } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState } from "@/components/shared/api-state";
import type { CmsPage } from "@/types/platform";

export default async function AboutPage() {
  let page: CmsPage | null = null;

  try {
    page = (await getPublicCmsPageBySlug("about")).data;
  } catch {
    return <ApiErrorState message="About content is not available right now." />;
  }

  return (
    <section className="space-y-6">
      <h1 className="text-3xl font-semibold">{page.title_en ?? page.title_ar}</h1>
      <SectionRenderer sections={page.sections} />
    </section>
  );
}

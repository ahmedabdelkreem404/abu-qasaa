import { getPublicCmsPageBySlug } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState } from "@/components/shared/api-state";
import type { CmsPage } from "@/types/platform";

export default async function HomePage() {
  let page: CmsPage | null = null;

  try {
    page = (await getPublicCmsPageBySlug("home")).data;
  } catch {
    return <ApiErrorState message="Home content is not available right now." />;
  }

  return <SectionRenderer sections={page.sections} />;
}

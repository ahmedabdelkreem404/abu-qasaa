import Link from "next/link";
import { getPublicCmsPageBySlug, listPublicBusinessUnits } from "@/api/client";
import { SectionRenderer } from "@/cms/section-renderer";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { BusinessUnit, CmsSection } from "@/types/platform";

async function loadBusinessUnits(): Promise<BusinessUnit[] | null> {
  try {
    const response = await listPublicBusinessUnits();
    return response.data;
  } catch {
    return null;
  }
}

export default async function BusinessUnitsPage() {
  const [businessUnits, cmsPage] = await Promise.all([
    loadBusinessUnits(),
    getPublicCmsPageBySlug("business-units").then((response) => response.data).catch(() => null),
  ]);
  const sections: CmsSection[] = cmsPage?.sections ?? [];

  if (businessUnits === null) {
    return <ApiErrorState message="Business units could not be loaded from the API." />;
  }

  return (
    <section className="space-y-6">
      {sections.length > 0 ? <SectionRenderer sections={sections} /> : <div>
        <h1 className="text-3xl font-semibold">Business Units</h1>
        <p className="mt-2 max-w-2xl text-slate-600">
          Active business units managed by Abnaa Abu Qasaa Trading.
        </p>
      </div>}
      {businessUnits.length === 0 ? (
        <EmptyState message="No active business units are published yet." />
      ) : (
        <div className="grid gap-4 md:grid-cols-2">
          {businessUnits.map((unit) => (
            <Link key={unit.id} href={`/${unit.slug}`} className="rounded-md border border-slate-200 bg-white p-5">
              <h2 className="font-semibold">{unit.name_en ?? unit.name_ar}</h2>
              <p className="mt-2 text-sm text-slate-600">{unit.type}</p>
            </Link>
          ))}
        </div>
      )}
    </section>
  );
}

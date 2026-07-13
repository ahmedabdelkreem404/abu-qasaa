import { listPublicPropertyUnits, listPublicRealEstateProjects } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import { getDictionary } from "@/i18n/server";
import Link from "next/link";

export default async function PublicRealEstatePage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const [projects, units, dictionary] = await Promise.all([
    listPublicRealEstateProjects(businessSlug).then((response) => response.data).catch(() => null),
    listPublicPropertyUnits(businessSlug, new URLSearchParams({ per_page: "6" })).then((response) => response.data).catch(() => []),
    getDictionary(),
  ]);

  if (projects === null) {
    return <ApiErrorState message="Real estate projects are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-eyebrow">{businessSlug}</p>
        <h1 className="aq-title">{dictionary.public.realEstateTitle}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{dictionary.public.realEstateBody}</p>
      </div>
      {projects.length === 0 ? <EmptyState message="No active projects yet." /> : (
        <div className="grid gap-4 md:grid-cols-2">
          {projects.map((project) => (
            <Link key={project.id} href={`/${businessSlug}/real-estate/projects/${project.slug}`} className="aq-card p-5 transition hover:-translate-y-1">
              <span className="aq-chip">{project.project_type}</span>
              <h2 className="mt-4 text-xl font-black">{project.name_en ?? project.name_ar}</h2>
              <p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{project.description_en ?? project.description_ar ?? project.project_type}</p>
              {project.starting_price ? <p className="mt-3 text-lg font-black text-[var(--aq-primary)]">From {project.starting_price} {project.currency}</p> : null}
            </Link>
          ))}
        </div>
      )}
      <div className="aq-grid-auto">
        {units.map((unit) => <div key={unit.id} className="aq-card p-5"><span className="aq-chip">{unit.status}</span><h3 className="mt-4 font-black">{unit.unit_code}</h3><p className="mt-2 text-sm text-[var(--aq-muted)]">{unit.bedrooms ?? "-"} bedrooms · {unit.area} m2</p><p className="mt-3 text-lg font-black text-[var(--aq-primary)]">{unit.price} {unit.currency}</p></div>)}
      </div>
    </section>
  );
}

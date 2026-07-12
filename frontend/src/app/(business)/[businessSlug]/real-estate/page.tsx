import { listPublicPropertyUnits, listPublicRealEstateProjects } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import Link from "next/link";

export default async function PublicRealEstatePage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const [projects, units] = await Promise.all([
    listPublicRealEstateProjects(businessSlug).then((response) => response.data).catch(() => null),
    listPublicPropertyUnits(businessSlug, new URLSearchParams({ per_page: "6" })).then((response) => response.data).catch(() => []),
  ]);

  if (projects === null) {
    return <ApiErrorState message="Real estate projects are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Real Estate</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Browse active projects and available units.</p>
      </div>
      {projects.length === 0 ? <EmptyState message="No active projects yet." /> : (
        <div className="grid gap-4 md:grid-cols-2">
          {projects.map((project) => (
            <Link key={project.id} href={`/${businessSlug}/real-estate/projects/${project.slug}`} className="rounded-md border border-slate-200 bg-white p-5">
              <h2 className="font-semibold">{project.name_en ?? project.name_ar}</h2>
              <p className="mt-2 text-sm text-slate-600">{project.description_en ?? project.description_ar ?? project.project_type}</p>
              {project.starting_price ? <p className="mt-3 text-sm font-medium text-teal-700">From {project.starting_price} {project.currency}</p> : null}
            </Link>
          ))}
        </div>
      )}
      <div className="grid gap-4 md:grid-cols-3">
        {units.map((unit) => <div key={unit.id} className="rounded-md border border-slate-200 bg-white p-5"><h3 className="font-semibold">{unit.unit_code}</h3><p className="mt-2 text-sm text-slate-600">{unit.bedrooms ?? "-"} bedrooms · {unit.area} m2</p><p className="mt-3 text-sm font-medium text-teal-700">{unit.price} {unit.currency}</p></div>)}
      </div>
    </section>
  );
}

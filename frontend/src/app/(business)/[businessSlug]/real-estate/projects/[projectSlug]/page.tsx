import { getPublicRealEstateProject } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";

export default async function PublicRealEstateProjectPage({ params }: { params: Promise<{ businessSlug: string; projectSlug: string }> }) {
  const { businessSlug, projectSlug } = await params;
  const project = await getPublicRealEstateProject(businessSlug, projectSlug).then((response) => response.data).catch(() => null);

  if (!project) {
    return <ApiErrorState message="Project is not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="text-sm font-medium uppercase tracking-wide text-teal-700">{project.project_type}</p>
        <h1 className="mt-2 text-3xl font-semibold">{project.name_en ?? project.name_ar}</h1>
        <p className="mt-3 max-w-2xl text-slate-600">{project.description_en ?? project.description_ar}</p>
      </div>
      <div className="grid gap-4 md:grid-cols-3">
        {(project.units ?? []).map((unit) => <div key={unit.id} className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">{unit.unit_code}</h2><p className="mt-2 text-sm text-slate-600">{unit.unit_type} · {unit.bedrooms ?? "-"} bedrooms · {unit.area} m2</p><p className="mt-3 text-sm font-medium text-teal-700">{unit.price} {unit.currency}</p></div>)}
      </div>
    </section>
  );
}

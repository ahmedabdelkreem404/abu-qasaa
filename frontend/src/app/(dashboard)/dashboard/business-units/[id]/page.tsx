import Link from "next/link";
import { getBusinessUnit } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { BusinessUnit } from "@/types/platform";

async function loadBusinessUnit(id: string): Promise<BusinessUnit | null> {
  try {
    const response = await getBusinessUnit(id);
    return response.data;
  } catch {
    return null;
  }
}

export default async function BusinessUnitDetailsPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const unit = await loadBusinessUnit(id);

  if (unit === null) {
    return <ApiErrorState message="Business unit could not be loaded." />;
  }

  return (
    <section className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-semibold">{unit.name_en ?? unit.name_ar}</h1>
          <p className="text-sm text-slate-600">{unit.slug} · {unit.type} · {unit.status}</p>
        </div>
        <div className="flex gap-2 text-sm text-teal-700">
          <Link href={`/dashboard/business-units/${unit.id}/edit`}>Edit</Link>
          <Link href={`/dashboard/business-units/${unit.id}/modules`}>Modules</Link>
          <Link href={`/dashboard/business-units/${unit.id}/settings`}>Settings</Link>
        </div>
      </div>
      <div className="grid gap-4 md:grid-cols-3">
        <div className="rounded-md border border-slate-200 bg-white p-4">
          <h2 className="font-medium">Basic information</h2>
          <p className="mt-2 text-sm text-slate-600">{unit.description ?? "No description yet."}</p>
        </div>
        <div className="rounded-md border border-slate-200 bg-white p-4">
          <h2 className="font-medium">Enabled modules</h2>
          <p className="mt-2 text-3xl font-semibold">{unit.enabled_modules_count ?? unit.modules?.filter((item) => item.is_enabled).length ?? 0}</p>
        </div>
        <div className="rounded-md border border-slate-200 bg-white p-4">
          <h2 className="font-medium">Settings</h2>
          <p className="mt-2 text-3xl font-semibold">{unit.settings?.length ?? 0}</p>
        </div>
      </div>
      <div className="rounded-md border border-slate-200 bg-white p-4">
        <h2 className="font-medium">Modules</h2>
        <div className="mt-3 flex flex-wrap gap-2 text-sm">
          {unit.modules?.map((module) => (
            <span key={module.id} className="rounded-md bg-slate-100 px-2 py-1">
              {module.key} {module.is_enabled ? "" : "(off)"}
            </span>
          ))}
        </div>
      </div>
    </section>
  );
}

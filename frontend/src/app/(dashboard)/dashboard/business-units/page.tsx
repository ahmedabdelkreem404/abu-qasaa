import Link from "next/link";
import { listBusinessUnits, toggleBusinessUnitStatus } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { BusinessUnit } from "@/types/platform";

async function loadBusinessUnits(): Promise<BusinessUnit[] | null> {
  try {
    const response = await listBusinessUnits();
    return response.data;
  } catch {
    return null;
  }
}

export default async function DashboardBusinessUnitsPage() {
  const businessUnits = await loadBusinessUnits();

  if (businessUnits === null) {
    return <ApiErrorState message="Business units could not be loaded. Check that the Laravel API is running." />;
  }

  return (
    <section className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-semibold">Business Units</h1>
          <p className="text-sm text-slate-600">Manage business units, templates, modules, and settings.</p>
        </div>
        <Link href="/dashboard/business-units/create" className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">
          Create
        </Link>
      </div>
      {businessUnits.length === 0 ? (
        <EmptyState message="No business units have been created yet." />
      ) : (
        <div className="overflow-hidden rounded-md border border-slate-200 bg-white">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-slate-600">
              <tr>
                <th className="px-4 py-3">Name</th>
                <th className="px-4 py-3">Type</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Modules</th>
                <th className="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              {businessUnits.map((unit) => (
                <tr key={unit.id} className="border-t border-slate-100">
                  <td className="px-4 py-3">
                    <Link href={`/dashboard/business-units/${unit.id}`} className="font-medium text-slate-950">
                      {unit.name_en ?? unit.name_ar}
                    </Link>
                    <div className="text-slate-500">{unit.slug}</div>
                  </td>
                  <td className="px-4 py-3">{unit.type}</td>
                  <td className="px-4 py-3">{unit.status}</td>
                  <td className="px-4 py-3">{unit.enabled_modules_count ?? 0}</td>
                  <td className="px-4 py-3">
                    <div className="flex flex-wrap gap-2 text-teal-700">
                      <Link href={`/dashboard/business-units/${unit.id}/edit`}>Edit</Link>
                      <Link href={`/dashboard/business-units/${unit.id}/modules`}>Modules</Link>
                      <Link href={`/dashboard/business-units/${unit.id}/settings`}>Settings</Link>
                      <form action={async () => {
                        "use server";
                        await toggleBusinessUnitStatus(unit.id);
                      }}>
                        <button className="text-teal-700">Toggle</button>
                      </form>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </section>
  );
}

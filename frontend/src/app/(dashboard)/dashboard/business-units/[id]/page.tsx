"use client";

import Link from "next/link";
import { getBusinessUnit } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { BusinessUnit } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function BusinessUnitDetailsPage() {
  const { id } = useParams<{ id: string }>();
  const [unit, setUnit] = useState<BusinessUnit | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    getBusinessUnit(id)
      .then((response) => setUnit(response.data))
      .catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Business unit could not be loaded."));
  }, [id]);

  if (error) return <ApiErrorState message={error} />;
  if (!unit) return <div className="text-sm text-slate-600">Loading business unit...</div>;

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
    </section>
  );
}

"use client";

import { useRouter } from "next/navigation";
import { FormEvent, useMemo, useState } from "react";
import { updateBusinessUnitModules } from "@/api/client";
import type { ActivityModule, BusinessUnitModule } from "@/types/platform";

export function ModuleManager({
  businessUnitId,
  modules,
  assignments,
}: {
  businessUnitId: number;
  modules: ActivityModule[];
  assignments: BusinessUnitModule[];
}) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const enabled = useMemo(
    () => new Set(assignments.filter((item) => item.is_enabled).map((item) => item.key)),
    [assignments],
  );

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const selected = new Set(form.getAll("modules").map(String));
    setIsSaving(true);
    setError(null);

    try {
      await updateBusinessUnitModules(
        businessUnitId,
        modules.map((module) => ({ key: module.key, is_enabled: selected.has(module.key) })),
      );
      router.refresh();
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Unable to update modules.");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      {error ? <p className="text-sm text-red-600">{error}</p> : null}
      <div className="grid gap-3 md:grid-cols-2">
        {modules.map((module) => (
          <label key={module.key} className="flex items-start gap-3 rounded-md border border-slate-200 bg-white p-4 text-sm">
            <input name="modules" type="checkbox" defaultChecked={enabled.has(module.key)} value={module.key} className="mt-1" />
            <span>
              <span className="block font-medium text-slate-900">{module.name}</span>
              <span className="text-slate-500">{module.category ?? "general"}</span>
            </span>
          </label>
        ))}
      </div>
      <button disabled={isSaving} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">
        {isSaving ? "Saving..." : "Save modules"}
      </button>
    </form>
  );
}

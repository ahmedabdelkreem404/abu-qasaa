"use client";

import { getActivityModules, getBusinessUnitModules } from "@/api/client";
import { ModuleManager } from "@/business-units/module-manager";
import { ApiErrorState } from "@/components/shared/api-state";
import type { ActivityModule, BusinessUnitModule } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function BusinessUnitModulesPage() {
  const { id } = useParams<{ id: string }>();
  const [modules, setModules] = useState<ActivityModule[] | null>(null);
  const [assignments, setAssignments] = useState<BusinessUnitModule[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([getActivityModules(), getBusinessUnitModules(id)])
      .then(([moduleResponse, assignmentResponse]) => {
        setModules(moduleResponse.data);
        setAssignments(assignmentResponse.data);
      })
      .catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Modules could not be loaded."));
  }, [id]);

  if (error) return <ApiErrorState message={error} />;
  if (!modules || !assignments) return <div className="text-sm text-slate-600">Loading modules...</div>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Business Unit Modules</h1>
      <ModuleManager businessUnitId={Number(id)} modules={modules} assignments={assignments} />
    </section>
  );
}

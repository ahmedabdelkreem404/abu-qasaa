import { getActivityModules, getBusinessUnitModules } from "@/api/client";
import { ModuleManager } from "@/business-units/module-manager";
import { ApiErrorState } from "@/components/shared/api-state";
import type { ActivityModule, BusinessUnitModule } from "@/types/platform";

async function loadModuleData(id: string): Promise<{ modules: ActivityModule[]; assignments: BusinessUnitModule[] } | null> {
  try {
    const [modules, assignments] = await Promise.all([
      getActivityModules(),
      getBusinessUnitModules(id),
    ]);

    return { modules: modules.data, assignments: assignments.data };
  } catch {
    return null;
  }
}

export default async function BusinessUnitModulesPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const data = await loadModuleData(id);

  if (data === null) {
    return <ApiErrorState message="Modules could not be loaded." />;
  }

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Business Unit Modules</h1>
      <ModuleManager businessUnitId={Number(id)} modules={data.modules} assignments={data.assignments} />
    </section>
  );
}

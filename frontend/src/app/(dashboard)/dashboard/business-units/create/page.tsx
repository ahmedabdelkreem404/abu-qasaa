import { getActivityTemplates } from "@/api/client";
import { BusinessUnitForm } from "@/business-units/business-unit-form";
import { ApiErrorState } from "@/components/shared/api-state";
import type { ActivityTemplate } from "@/types/platform";

async function loadTemplates(): Promise<ActivityTemplate[] | null> {
  try {
    const templates = await getActivityTemplates();
    return templates.data;
  } catch {
    return null;
  }
}

export default async function CreateBusinessUnitPage() {
  const templates = await loadTemplates();

  if (templates === null) {
    return <ApiErrorState message="Activity templates could not be loaded." />;
  }

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Create Business Unit</h1>
      <BusinessUnitForm templates={templates} />
    </section>
  );
}

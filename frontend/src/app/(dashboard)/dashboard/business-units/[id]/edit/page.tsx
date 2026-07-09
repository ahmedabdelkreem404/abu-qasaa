import { getActivityTemplates, getBusinessUnit } from "@/api/client";
import { BusinessUnitForm } from "@/business-units/business-unit-form";
import { ApiErrorState } from "@/components/shared/api-state";
import type { ActivityTemplate, BusinessUnit } from "@/types/platform";

async function loadEditData(id: string): Promise<{ businessUnit: BusinessUnit; templates: ActivityTemplate[] } | null> {
  try {
    const [businessUnit, templates] = await Promise.all([
      getBusinessUnit(id),
      getActivityTemplates(),
    ]);

    return { businessUnit: businessUnit.data, templates: templates.data };
  } catch {
    return null;
  }
}

export default async function EditBusinessUnitPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const data = await loadEditData(id);

  if (data === null) {
    return <ApiErrorState message="Business unit could not be loaded for editing." />;
  }

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Edit Business Unit</h1>
      <BusinessUnitForm businessUnit={data.businessUnit} templates={data.templates} />
    </section>
  );
}

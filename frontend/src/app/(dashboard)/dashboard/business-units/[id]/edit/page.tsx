"use client";

import { getActivityTemplates, getBusinessUnit } from "@/api/client";
import { BusinessUnitForm } from "@/business-units/business-unit-form";
import { ApiErrorState } from "@/components/shared/api-state";
import type { ActivityTemplate, BusinessUnit } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function EditBusinessUnitPage() {
  const { id } = useParams<{ id: string }>();
  const [businessUnit, setBusinessUnit] = useState<BusinessUnit | null>(null);
  const [templates, setTemplates] = useState<ActivityTemplate[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([getBusinessUnit(id), getActivityTemplates()])
      .then(([unitResponse, templateResponse]) => {
        setBusinessUnit(unitResponse.data);
        setTemplates(templateResponse.data);
      })
      .catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Business unit could not be loaded for editing."));
  }, [id]);

  if (error) return <ApiErrorState message={error} />;
  if (!businessUnit || !templates) return <div className="text-sm text-slate-600">Loading edit form...</div>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Edit Business Unit</h1>
      <BusinessUnitForm businessUnit={businessUnit} templates={templates} />
    </section>
  );
}

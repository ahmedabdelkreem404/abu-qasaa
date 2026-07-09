"use client";

import { getActivityTemplates } from "@/api/client";
import { BusinessUnitForm } from "@/business-units/business-unit-form";
import { ApiErrorState } from "@/components/shared/api-state";
import type { ActivityTemplate } from "@/types/platform";
import { useEffect, useState } from "react";

export default function CreateBusinessUnitPage() {
  const [templates, setTemplates] = useState<ActivityTemplate[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    getActivityTemplates()
      .then((response) => setTemplates(response.data))
      .catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Activity templates could not be loaded."));
  }, []);

  if (error) return <ApiErrorState message={error} />;
  if (templates === null) return <div className="text-sm text-slate-600">Loading templates...</div>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Create Business Unit</h1>
      <BusinessUnitForm templates={templates} />
    </section>
  );
}

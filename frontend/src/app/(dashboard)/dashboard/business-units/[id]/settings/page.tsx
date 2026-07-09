"use client";

import { getBusinessUnitSettings, listFeatureFlags } from "@/api/client";
import { SettingsManager } from "@/business-units/settings-manager";
import { ApiErrorState } from "@/components/shared/api-state";
import type { BusinessUnitSetting, FeatureFlag } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function BusinessUnitSettingsPage() {
  const { id } = useParams<{ id: string }>();
  const [settings, setSettings] = useState<BusinessUnitSetting[] | null>(null);
  const [featureFlags, setFeatureFlags] = useState<FeatureFlag[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([getBusinessUnitSettings(id), listFeatureFlags()])
      .then(([settingsResponse, flagsResponse]) => {
        setSettings(settingsResponse.data);
        setFeatureFlags(flagsResponse.data);
      })
      .catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Settings could not be loaded."));
  }, [id]);

  if (error) return <ApiErrorState message={error} />;
  if (!settings || !featureFlags) return <div className="text-sm text-slate-600">Loading settings...</div>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Business Unit Settings</h1>
      <SettingsManager
        businessUnitId={Number(id)}
        settings={settings}
        featureFlags={featureFlags.filter((flag) => flag.business_unit_id === null || flag.business_unit_id === Number(id))}
      />
    </section>
  );
}

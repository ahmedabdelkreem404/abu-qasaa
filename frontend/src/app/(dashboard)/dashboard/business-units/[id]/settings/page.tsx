import { getBusinessUnitSettings, listFeatureFlags } from "@/api/client";
import { SettingsManager } from "@/business-units/settings-manager";
import { ApiErrorState } from "@/components/shared/api-state";
import type { BusinessUnitSetting, FeatureFlag } from "@/types/platform";

async function loadSettingsData(id: string): Promise<{ settings: BusinessUnitSetting[]; featureFlags: FeatureFlag[] } | null> {
  try {
    const [settings, featureFlags] = await Promise.all([
      getBusinessUnitSettings(id),
      listFeatureFlags(),
    ]);

    return { settings: settings.data, featureFlags: featureFlags.data };
  } catch {
    return null;
  }
}

export default async function BusinessUnitSettingsPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const data = await loadSettingsData(id);

  if (data === null) {
    return <ApiErrorState message="Settings could not be loaded." />;
  }

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Business Unit Settings</h1>
      <SettingsManager
        businessUnitId={Number(id)}
        settings={data.settings}
        featureFlags={data.featureFlags.filter((flag) => flag.business_unit_id === null || flag.business_unit_id === Number(id))}
      />
    </section>
  );
}

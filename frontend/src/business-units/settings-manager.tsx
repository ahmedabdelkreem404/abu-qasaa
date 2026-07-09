"use client";

import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";
import { updateBusinessUnitSettings } from "@/api/client";
import type { BusinessUnitSetting, FeatureFlag } from "@/types/platform";

const settingKeys = [
  "registration_enabled",
  "checkout_enabled",
  "show_prices",
  "allow_guest_checkout",
  "manual_payment_enabled",
  "paymob_enabled",
  "inventory_enabled",
  "wholesale_enabled",
  "appointments_enabled",
  "rfq_enabled",
];

export function SettingsManager({
  businessUnitId,
  settings,
  featureFlags,
}: {
  businessUnitId: number;
  settings: BusinessUnitSetting[];
  featureFlags: FeatureFlag[];
}) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const values = new Map(settings.map((setting) => [setting.key, Boolean(setting.value)]));

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const nextSettings = Object.fromEntries(
      settingKeys.map((key) => [key, form.get(key) === "on"]),
    );

    setIsSaving(true);
    setError(null);

    try {
      await updateBusinessUnitSettings(businessUnitId, nextSettings);
      router.refresh();
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Unable to update settings.");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <div className="space-y-6">
      <form onSubmit={onSubmit} className="space-y-4">
        {error ? <p className="text-sm text-red-600">{error}</p> : null}
        <div className="grid gap-3 md:grid-cols-2">
          {settingKeys.map((key) => (
            <label key={key} className="flex items-center justify-between rounded-md border border-slate-200 bg-white p-4 text-sm">
              <span>{key}</span>
              <input name={key} type="checkbox" defaultChecked={values.get(key) ?? false} />
            </label>
          ))}
        </div>
        <button disabled={isSaving} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">
          {isSaving ? "Saving..." : "Save settings"}
        </button>
      </form>
      <section className="space-y-3">
        <h2 className="text-lg font-semibold">Related feature flags</h2>
        <div className="grid gap-2">
          {featureFlags.map((flag) => (
            <div key={flag.id} className="rounded-md border border-slate-200 bg-white p-3 text-sm">
              {flag.key}: {flag.value ? "enabled" : "disabled"}
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}

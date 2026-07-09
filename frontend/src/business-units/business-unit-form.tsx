"use client";

import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";
import {
  createBusinessUnit,
  updateBusinessUnit,
  type BusinessUnitPayload,
} from "@/api/client";
import type { ActivityTemplate, BusinessUnit } from "@/types/platform";

const types = [
  "product_store",
  "wholesale_store",
  "services_rfq",
  "real_estate",
  "content_only",
  "hybrid",
];

const statuses = ["active", "inactive", "draft", "archived"];

export function BusinessUnitForm({
  templates,
  businessUnit,
}: {
  templates: ActivityTemplate[];
  businessUnit?: BusinessUnit;
}) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setIsSaving(true);

    const form = new FormData(event.currentTarget);
    const payload: BusinessUnitPayload = {
      name_ar: String(form.get("name_ar") ?? ""),
      name_en: String(form.get("name_en") ?? "") || null,
      slug: String(form.get("slug") ?? ""),
      type: String(form.get("type") ?? "product_store"),
      status: String(form.get("status") ?? "draft"),
      description: String(form.get("description") ?? "") || null,
      primary_color: String(form.get("primary_color") ?? "") || null,
      secondary_color: String(form.get("secondary_color") ?? "") || null,
      template_key: String(form.get("template_key") ?? "") || null,
    };

    try {
      if (businessUnit) {
        await updateBusinessUnit(businessUnit.id, payload);
        router.push(`/dashboard/business-units/${businessUnit.id}`);
      } else {
        const response = await createBusinessUnit(payload);
        router.push(`/dashboard/business-units/${response.data.id}`);
      }
      router.refresh();
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Unable to save.");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <form onSubmit={onSubmit} className="grid gap-4 rounded-md border border-slate-200 bg-white p-5">
      {error ? <p className="text-sm text-red-600">{error}</p> : null}
      <label className="grid gap-1 text-sm">
        Arabic name
        <input name="name_ar" required defaultValue={businessUnit?.name_ar} className="rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <label className="grid gap-1 text-sm">
        English name
        <input name="name_en" defaultValue={businessUnit?.name_en ?? ""} className="rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <label className="grid gap-1 text-sm">
        Slug
        <input name="slug" required defaultValue={businessUnit?.slug} className="rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <div className="grid gap-4 md:grid-cols-3">
        <label className="grid gap-1 text-sm">
          Template
          <select name="template_key" defaultValue="" className="rounded-md border border-slate-300 px-3 py-2">
            <option value="">None</option>
            {templates.map((template) => (
              <option key={template.key} value={template.key}>{template.name}</option>
            ))}
          </select>
        </label>
        <label className="grid gap-1 text-sm">
          Type
          <select name="type" defaultValue={businessUnit?.type ?? "product_store"} className="rounded-md border border-slate-300 px-3 py-2">
            {types.map((type) => <option key={type} value={type}>{type}</option>)}
          </select>
        </label>
        <label className="grid gap-1 text-sm">
          Status
          <select name="status" defaultValue={businessUnit?.status ?? "draft"} className="rounded-md border border-slate-300 px-3 py-2">
            {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
          </select>
        </label>
      </div>
      <div className="grid gap-4 md:grid-cols-2">
        <label className="grid gap-1 text-sm">
          Primary color
          <input name="primary_color" defaultValue={businessUnit?.primary_color ?? ""} className="rounded-md border border-slate-300 px-3 py-2" />
        </label>
        <label className="grid gap-1 text-sm">
          Secondary color
          <input name="secondary_color" defaultValue={businessUnit?.secondary_color ?? ""} className="rounded-md border border-slate-300 px-3 py-2" />
        </label>
      </div>
      <label className="grid gap-1 text-sm">
        Description
        <textarea name="description" defaultValue={businessUnit?.description ?? ""} className="min-h-28 rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <button disabled={isSaving} className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">
        {isSaving ? "Saving..." : "Save business unit"}
      </button>
    </form>
  );
}

"use client";

import { createCmsPage, updateCmsPage, type CmsPagePayload } from "@/api/client";
import type { CmsPage, CmsPageStatus, CmsPageType } from "@/types/platform";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

export function CmsPageForm({ page }: { page?: CmsPage }) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload: CmsPagePayload = {
      business_unit_id: form.get("business_unit_id") ? Number(form.get("business_unit_id")) : null,
      title_ar: String(form.get("title_ar") ?? ""),
      title_en: String(form.get("title_en") ?? "") || null,
      slug: String(form.get("slug") ?? ""),
      page_type: String(form.get("page_type") ?? "standard") as CmsPageType,
      status: String(form.get("status") ?? "draft") as CmsPageStatus,
      excerpt_ar: String(form.get("excerpt_ar") ?? "") || null,
      excerpt_en: String(form.get("excerpt_en") ?? "") || null,
      seo_title_en: String(form.get("seo_title_en") ?? "") || null,
      seo_description_en: String(form.get("seo_description_en") ?? "") || null,
    };

    setSaving(true);
    setError(null);
    try {
      const response = page ? await updateCmsPage(page.id, payload) : await createCmsPage(payload);
      router.push(`/dashboard/cms/pages/${response.data.id}`);
      router.refresh();
    } catch (caught) {
      setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Could not save CMS page.");
    } finally {
      setSaving(false);
    }
  }

  return (
    <form onSubmit={onSubmit} className="grid gap-4 rounded-md border border-slate-200 bg-white p-5">
      {error ? <p className="text-sm text-red-600">{error}</p> : null}
      <label className="grid gap-1 text-sm">Business unit ID
        <input name="business_unit_id" defaultValue={page?.business_unit_id ?? ""} className="rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <label className="grid gap-1 text-sm">Arabic title
        <input name="title_ar" required defaultValue={page?.title_ar} className="rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <label className="grid gap-1 text-sm">English title
        <input name="title_en" defaultValue={page?.title_en ?? ""} className="rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <div className="grid gap-4 md:grid-cols-3">
        <label className="grid gap-1 text-sm">Slug
          <input name="slug" required defaultValue={page?.slug} className="rounded-md border border-slate-300 px-3 py-2" />
        </label>
        <label className="grid gap-1 text-sm">Type
          <select name="page_type" defaultValue={page?.page_type ?? "standard"} className="rounded-md border border-slate-300 px-3 py-2">
            {["home", "about", "contact", "business_unit_landing", "standard", "custom"].map((value) => <option key={value} value={value}>{value}</option>)}
          </select>
        </label>
        <label className="grid gap-1 text-sm">Status
          <select name="status" defaultValue={page?.status ?? "draft"} className="rounded-md border border-slate-300 px-3 py-2">
            {["draft", "published", "archived"].map((value) => <option key={value} value={value}>{value}</option>)}
          </select>
        </label>
      </div>
      <label className="grid gap-1 text-sm">English excerpt
        <textarea name="excerpt_en" defaultValue={page?.excerpt_en ?? ""} className="min-h-20 rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <label className="grid gap-1 text-sm">SEO title
        <input name="seo_title_en" defaultValue={page?.seo_title_en ?? ""} className="rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <label className="grid gap-1 text-sm">SEO description
        <textarea name="seo_description_en" defaultValue={page?.seo_description_en ?? ""} className="min-h-20 rounded-md border border-slate-300 px-3 py-2" />
      </label>
      <button disabled={saving} className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">
        {saving ? "Saving..." : "Save page"}
      </button>
    </form>
  );
}

"use client";

import { updateCmsPageSections } from "@/api/client";
import type { CmsPage, CmsSection } from "@/types/platform";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

export function CmsSectionsForm({ page }: { page: CmsPage }) {
  const router = useRouter();
  const [json, setJson] = useState(JSON.stringify(page.sections ?? [], null, 2));
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    try {
      const sections = JSON.parse(json) as CmsSection[];
      await updateCmsPageSections(page.id, sections);
      router.push(`/dashboard/cms/pages/${page.id}`);
      router.refresh();
    } catch {
      setError("Could not save sections. Check the JSON shape.");
    }
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      {error ? <p className="text-sm text-red-600">{error}</p> : null}
      <textarea value={json} onChange={(event) => setJson(event.target.value)} className="min-h-[420px] w-full rounded-md border border-slate-300 p-3 font-mono text-sm" />
      <button className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Save sections</button>
    </form>
  );
}

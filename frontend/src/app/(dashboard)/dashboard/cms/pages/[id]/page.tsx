"use client";

import { getCmsPage, publishCmsPage } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import { SectionRenderer } from "@/cms/section-renderer";
import type { CmsPage } from "@/types/platform";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { useEffect, useState } from "react";

export default function CmsPageDetailsPage() {
  const { id } = useParams<{ id: string }>();
  const router = useRouter();
  const [page, setPage] = useState<CmsPage | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [publishing, setPublishing] = useState(false);

  useEffect(() => {
    getCmsPage(id)
      .then((response) => setPage(response.data))
      .catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "CMS page could not be loaded."));
  }, [id]);

  async function onPublish() {
    setPublishing(true);
    setError(null);
    try {
      const response = await publishCmsPage(id);
      setPage(response.data);
      router.refresh();
    } catch {
      setError("Could not publish CMS page.");
    } finally {
      setPublishing(false);
    }
  }

  if (error) return <ApiErrorState message={error} />;
  if (!page) return <div className="text-sm text-slate-600">Loading CMS page...</div>;

  return (
    <section className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-semibold">{page.title_en ?? page.title_ar}</h1>
          <p className="text-sm text-slate-600">{page.slug} &middot; {page.page_type} &middot; {page.status}</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Link href={`/dashboard/cms/pages/${page.id}/edit`} className="rounded-md border border-slate-300 px-3 py-2 text-sm">Edit</Link>
          <Link href={`/dashboard/cms/pages/${page.id}/sections`} className="rounded-md border border-slate-300 px-3 py-2 text-sm">Sections</Link>
          <button onClick={onPublish} disabled={publishing} className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white disabled:opacity-60">
            {publishing ? "Publishing..." : "Publish"}
          </button>
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <div className="rounded-md border border-slate-200 bg-white p-4">
          <h2 className="text-sm font-medium text-slate-500">Scope</h2>
          <p className="mt-2 font-medium">{page.business_unit?.name_en ?? page.business_unit?.name_ar ?? "Company"}</p>
        </div>
        <div className="rounded-md border border-slate-200 bg-white p-4">
          <h2 className="text-sm font-medium text-slate-500">Published at</h2>
          <p className="mt-2 font-medium">{page.published_at ?? "-"}</p>
        </div>
        <div className="rounded-md border border-slate-200 bg-white p-4">
          <h2 className="text-sm font-medium text-slate-500">Sections</h2>
          <p className="mt-2 text-3xl font-semibold">{page.sections?.length ?? 0}</p>
        </div>
      </div>

      {page.excerpt_en || page.excerpt_ar ? <p className="max-w-3xl text-slate-700">{page.excerpt_en ?? page.excerpt_ar}</p> : null}
      <SectionRenderer sections={page.sections ?? []} />
    </section>
  );
}

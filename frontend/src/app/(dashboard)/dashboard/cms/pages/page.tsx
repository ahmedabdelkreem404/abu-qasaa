"use client";

import { listCmsPages } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import type { CmsPage } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function CmsPagesPage() {
  const [pages, setPages] = useState<CmsPage[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const timeout = window.setTimeout(() => {
      listCmsPages().then((response) => setPages(response.data)).catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Could not load CMS pages."));
    }, 0);
    return () => window.clearTimeout(timeout);
  }, []);

  if (error) return <ApiErrorState message={error} />;
  if (!pages) return <div className="text-sm text-slate-600">Loading CMS pages...</div>;

  return (
    <section className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">CMS Pages</h1>
        <Link href="/dashboard/cms/pages/create" className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Create</Link>
      </div>
      <div className="overflow-hidden rounded-md border border-slate-200 bg-white">
        <table className="w-full text-left text-sm">
          <thead className="bg-slate-50"><tr><th className="p-3">Title</th><th>Type</th><th>Status</th><th>Business Unit</th><th>Published</th></tr></thead>
          <tbody>
            {pages.map((page) => (
              <tr key={page.id} className="border-t border-slate-100">
                <td className="p-3"><Link href={`/dashboard/cms/pages/${page.id}`} className="font-medium text-teal-700">{page.title_en ?? page.title_ar}</Link></td>
                <td>{page.page_type}</td>
                <td>{page.status}</td>
                <td>{page.business_unit?.slug ?? "Company"}</td>
                <td>{page.published_at ?? "-"}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}

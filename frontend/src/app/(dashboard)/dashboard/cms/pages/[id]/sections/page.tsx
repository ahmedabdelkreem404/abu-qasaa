"use client";

import { getCmsPage } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import { CmsSectionsForm } from "@/cms/cms-sections-form";
import type { CmsPage } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function EditCmsSectionsPage() {
  const { id } = useParams<{ id: string }>();
  const [page, setPage] = useState<CmsPage | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    getCmsPage(id)
      .then((response) => setPage(response.data))
      .catch((caught) => setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "CMS page could not be loaded."));
  }, [id]);

  if (error) return <ApiErrorState message={error} />;
  if (!page) return <div className="text-sm text-slate-600">Loading CMS page...</div>;

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold">Edit Sections</h1>
        <p className="text-sm text-slate-600">{page.title_en ?? page.title_ar}</p>
      </div>
      <CmsSectionsForm page={page} />
    </section>
  );
}

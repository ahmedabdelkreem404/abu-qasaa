"use client";

import { getCmsPage } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import { CmsPageForm } from "@/cms/cms-page-form";
import type { CmsPage } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function EditCmsPagePage() {
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
      <h1 className="text-2xl font-semibold">Edit CMS Page</h1>
      <CmsPageForm page={page} />
    </section>
  );
}

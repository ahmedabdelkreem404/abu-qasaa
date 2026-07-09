"use client";

import { getCategory } from "@/api/client";
import { CategoryForm } from "@/catalog/catalog-forms";
import { ApiErrorState } from "@/components/shared/api-state";
import type { Category } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function EditCategoryPage() {
  const { id } = useParams<{ id: string }>();
  const [item, setItem] = useState<Category | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { getCategory(id).then((r) => setItem(r.data)).catch(() => setError("Could not load category.")); }, [id]);
  if (error) return <ApiErrorState message={error} />;
  if (!item) return <div className="text-sm text-slate-600">Loading category...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Edit Category</h1><CategoryForm category={item} /></section>;
}

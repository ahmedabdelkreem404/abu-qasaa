"use client";

import { getBrand } from "@/api/client";
import { BrandForm } from "@/catalog/catalog-forms";
import { ApiErrorState } from "@/components/shared/api-state";
import type { Brand } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function EditBrandPage() {
  const { id } = useParams<{ id: string }>();
  const [item, setItem] = useState<Brand | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { getBrand(id).then((r) => setItem(r.data)).catch(() => setError("Could not load brand.")); }, [id]);
  if (error) return <ApiErrorState message={error} />;
  if (!item) return <div className="text-sm text-slate-600">Loading brand...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Edit Brand</h1><BrandForm brand={item} /></section>;
}

"use client";

import { getPriceList } from "@/api/client";
import { PriceListForm } from "@/catalog/catalog-forms";
import { ApiErrorState } from "@/components/shared/api-state";
import type { PriceList } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function EditPriceListPage() {
  const { id } = useParams<{ id: string }>();
  const [item, setItem] = useState<PriceList | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { getPriceList(id).then((r) => setItem(r.data)).catch(() => setError("Could not load price list.")); }, [id]);
  if (error) return <ApiErrorState message={error} />;
  if (!item) return <div className="text-sm text-slate-600">Loading price list...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Edit Price List</h1><PriceListForm priceList={item} /></section>;
}

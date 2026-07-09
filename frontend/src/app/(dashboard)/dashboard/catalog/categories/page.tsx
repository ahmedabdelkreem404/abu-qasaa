"use client";

import { listCategories } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { Category } from "@/types/platform";
import Link from "next/link";
import { useEffect, useState } from "react";

export default function CategoriesPage() {
  const [items, setItems] = useState<Category[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { listCategories().then((r) => setItems(r.data)).catch((e) => setError(e instanceof Error && e.name === "403" ? "Forbidden." : "Could not load categories.")); }, []);
  if (error) return <ApiErrorState message={error} />;
  if (!items) return <div className="text-sm text-slate-600">Loading categories...</div>;
  return <section className="space-y-6">
    <Header title="Categories" href="/dashboard/catalog/categories/create" />
    {items.length === 0 ? <EmptyState message="No categories yet." /> : <CatalogTable rows={items.map((item) => [item.id, item.name_en ?? item.name_ar, item.slug, item.business_unit?.slug ?? item.business_unit_id, item.status, `/dashboard/catalog/categories/${item.id}/edit`])} />}
  </section>;
}

function Header({ title, href }: { title: string; href: string }) {
  return <div className="flex items-center justify-between"><h1 className="text-2xl font-semibold">{title}</h1><Link href={href} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Create</Link></div>;
}

function CatalogTable({ rows }: { rows: Array<Array<string | number>> }) {
  return <div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Name</th><th>Slug</th><th>Business Unit</th><th>Status</th></tr></thead><tbody>{rows.map(([id, name, slug, unit, status, href]) => <tr key={id} className="border-t border-slate-100"><td className="p-3"><Link href={String(href)} className="font-medium text-teal-700">{name}</Link></td><td>{slug}</td><td>{unit}</td><td>{status}</td></tr>)}</tbody></table></div>;
}

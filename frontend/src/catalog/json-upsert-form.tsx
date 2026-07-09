"use client";

import { updateProductPrices, updateProductVariants } from "@/api/client";
import type { Product, ProductPrice, ProductVariant } from "@/types/platform";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

export function JsonUpsertForm({ product, kind }: { product: Product; kind: "variants" | "prices" }) {
  const router = useRouter();
  const initial = kind === "variants" ? product.variants ?? [] : product.prices ?? [];
  const [json, setJson] = useState(JSON.stringify(initial, null, 2));
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    try {
      const parsed = JSON.parse(json);
      if (kind === "variants") {
        await updateProductVariants(product.id, parsed as ProductVariant[]);
      } else {
        await updateProductPrices(product.id, parsed as ProductPrice[]);
      }
      router.push(`/dashboard/catalog/products/${product.id}`);
      router.refresh();
    } catch {
      setError(`Could not save ${kind}. Check JSON and business-unit rules.`);
    }
  }

  return <form onSubmit={onSubmit} className="space-y-4">
    {error ? <p className="text-sm text-red-600">{error}</p> : null}
    <textarea value={json} onChange={(event) => setJson(event.target.value)} className="min-h-[420px] w-full rounded-md border border-slate-300 p-3 font-mono text-sm" />
    <button className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Save {kind}</button>
  </form>;
}

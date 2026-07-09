"use client";

import { getProduct } from "@/api/client";
import { ProductForm } from "@/catalog/catalog-forms";
import { ApiErrorState } from "@/components/shared/api-state";
import type { Product } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function EditProductPage() {
  const { id } = useParams<{ id: string }>();
  const [product, setProduct] = useState<Product | null>(null);
  const [error, setError] = useState<string | null>(null);
  useEffect(() => { getProduct(id).then((r) => setProduct(r.data)).catch(() => setError("Could not load product.")); }, [id]);
  if (error) return <ApiErrorState message={error} />;
  if (!product) return <div className="text-sm text-slate-600">Loading product...</div>;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Edit Product</h1><ProductForm product={product} /></section>;
}

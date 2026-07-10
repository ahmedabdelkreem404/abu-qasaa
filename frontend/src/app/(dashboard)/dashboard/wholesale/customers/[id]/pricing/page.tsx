"use client";

import { getWholesalePricingPreview } from "@/api/client";
import type { WholesalePricing } from "@/types/platform";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function WholesalePricingPreviewPage() {
  const { id } = useParams<{ id: string }>();
  const [items, setItems] = useState<WholesalePricing[] | null>(null);

  useEffect(() => { getWholesalePricingPreview(id).then((r) => setItems(r.data)).catch(() => setItems([])); }, [id]);
  if (!items) return <p className="text-sm text-slate-600">Loading pricing preview...</p>;

  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Pricing preview</h1><div className="overflow-hidden rounded-md border border-slate-200 bg-white"><table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr><th className="p-3">Product</th><th>Wholesale Price</th><th>Minimum</th><th>Source</th></tr></thead><tbody>{items.map((item) => <tr key={item.product_id} className="border-t border-slate-100"><td className="p-3">{item.name_en ?? item.name_ar}</td><td>{item.wholesale_price} {item.currency}</td><td>{item.min_quantity_applied}</td><td>{item.price_source}</td></tr>)}</tbody></table></div></section>;
}

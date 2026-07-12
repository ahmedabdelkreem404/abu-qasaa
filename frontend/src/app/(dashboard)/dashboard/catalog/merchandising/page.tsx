"use client";

import { listCorporateGiftInquiries, listProductBadges, listProductBundles, listProductCollections } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { CorporateGiftInquiry, ProductBadge, ProductBundle, ProductCollection } from "@/types/platform";
import { useEffect, useState } from "react";

export default function MerchandisingPage() {
  const [data, setData] = useState<{
    badges: ProductBadge[];
    collections: ProductCollection[];
    bundles: ProductBundle[];
    inquiries: CorporateGiftInquiry[];
  } | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([
      listProductBadges(),
      listProductCollections(),
      listProductBundles(),
      listCorporateGiftInquiries(),
    ]).then(([badges, collections, bundles, inquiries]) => setData({
      badges: badges.data,
      collections: collections.data,
      bundles: bundles.data,
      inquiries: inquiries.data,
    })).catch((event) => setError(event instanceof Error && event.name === "403" ? "Forbidden." : "Could not load merchandising."));
  }, []);

  if (error) return <ApiErrorState message={error} />;
  if (!data) return <div className="text-sm text-slate-600">Loading merchandising...</div>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Merchandising</h1>
      <div className="grid gap-4 lg:grid-cols-2">
        <Panel title="Badges" empty="No badges yet." rows={data.badges.map((badge) => [badge.key, badge.name_en ?? badge.name_ar, badge.is_active ? "Active" : "Inactive"])} />
        <Panel title="Collections" empty="No collections yet." rows={data.collections.map((collection) => [collection.slug, collection.name_en ?? collection.name_ar, collection.status ?? "active"])} />
        <Panel title="Bundles" empty="No bundles yet." rows={data.bundles.map((bundle) => [String(bundle.product_id), bundle.name_en ?? bundle.name_ar, bundle.bundle_type])} />
        <Panel title="Corporate Gift Inquiries" empty="No inquiries yet." rows={data.inquiries.map((inquiry) => [inquiry.company_name ?? inquiry.contact_name, inquiry.phone, inquiry.status])} />
      </div>
    </section>
  );
}

function Panel({ title, empty, rows }: { title: string; empty: string; rows: string[][] }) {
  return (
    <div className="rounded-md border border-slate-200 bg-white p-5">
      <h2 className="font-semibold">{title}</h2>
      {rows.length === 0 ? <EmptyState message={empty} /> : (
        <div className="mt-4 overflow-hidden rounded-md border border-slate-200">
          <table className="w-full text-left text-sm">
            <tbody>{rows.map((row, index) => <tr key={`${row.join("-")}-${index}`} className="border-t border-slate-100 first:border-t-0">{row.map((cell, cellIndex) => <td key={cellIndex} className="p-3">{cell}</td>)}</tr>)}</tbody>
          </table>
        </div>
      )}
    </div>
  );
}

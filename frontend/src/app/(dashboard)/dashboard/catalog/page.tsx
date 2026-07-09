import Link from "next/link";

const links = [
  ["/dashboard/catalog/products", "Products", "Manage product records, publishing, variants, and pricing."],
  ["/dashboard/catalog/categories", "Categories", "Manage business-unit scoped category trees."],
  ["/dashboard/catalog/brands", "Brands", "Manage catalog brands per business unit."],
  ["/dashboard/catalog/price-lists", "Price Lists", "Manage retail, wholesale, distributor, and special prices."],
];

export default function DashboardCatalogPage() {
  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Catalog</h1>
      <div className="grid gap-4 md:grid-cols-2">
        {links.map(([href, title, description]) => (
          <Link key={href} href={href} className="rounded-md border border-slate-200 bg-white p-5">
            <h2 className="font-semibold">{title}</h2>
            <p className="mt-2 text-sm text-slate-600">{description}</p>
          </Link>
        ))}
      </div>
    </section>
  );
}

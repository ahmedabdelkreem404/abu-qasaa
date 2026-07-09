import Link from "next/link";
import type { ReactNode } from "react";

const navItems = [
  { href: "/dashboard", label: "Overview" },
  { href: "/dashboard/business-units", label: "Business Units" },
  { href: "/dashboard/users", label: "Users" },
  { href: "/dashboard/catalog", label: "Catalog" },
  { href: "/dashboard/orders", label: "Orders" },
  { href: "/dashboard/payments", label: "Payments" },
  { href: "/dashboard/inventory", label: "Inventory" },
  { href: "/dashboard/services-rfq", label: "Services RFQ" },
  { href: "/dashboard/real-estate", label: "Real Estate" },
  { href: "/dashboard/cms", label: "CMS" },
  { href: "/dashboard/reports", label: "Reports" },
  { href: "/dashboard/settings", label: "Settings" },
  { href: "/dashboard/features", label: "Features" },
];

export function DashboardShell({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-slate-100 text-slate-950 lg:grid lg:grid-cols-[260px_1fr]">
      <aside className="border-r border-slate-200 bg-white px-4 py-5">
        <Link href="/dashboard" className="block font-semibold">
          Abnaa Admin
        </Link>
        <nav className="mt-6 grid gap-1 text-sm">
          {navItems.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className="rounded-md px-3 py-2 text-slate-700 hover:bg-slate-100"
            >
              {item.label}
            </Link>
          ))}
        </nav>
      </aside>
      <main className="min-w-0 px-4 py-6 sm:px-8">{children}</main>
    </div>
  );
}

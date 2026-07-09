"use client";

import { logout } from "@/api/client";
import { ProtectedDashboard, useAuth } from "@/auth/auth-provider";
import Link from "next/link";
import type { ReactNode } from "react";
import { useRouter } from "next/navigation";

const navItems = [
  { href: "/dashboard", label: "Overview", permission: null },
  { href: "/dashboard/business-units", label: "Business Units", permission: "business_units.view" },
  { href: "/dashboard/users", label: "Users", permission: "users.view" },
  { href: "/dashboard/catalog", label: "Catalog", permission: "products.view" },
  { href: "/dashboard/orders", label: "Orders", permission: "orders.view" },
  { href: "/dashboard/payments", label: "Payments", permission: "payments.view" },
  { href: "/dashboard/inventory", label: "Inventory", permission: "inventory.view" },
  { href: "/dashboard/services-rfq", label: "Services RFQ", permission: "rfq.view" },
  { href: "/dashboard/real-estate", label: "Real Estate", permission: "real_estate.view" },
  { href: "/dashboard/cms", label: "CMS", permission: "cms.view" },
  { href: "/dashboard/reports", label: "Reports", permission: "reports.view" },
  { href: "/dashboard/settings", label: "Settings", permission: "settings.view" },
  { href: "/dashboard/features", label: "Features", permission: "settings.view" },
];

export function DashboardShell({ children }: { children: ReactNode }) {
  const { user, hasPermission } = useAuth();
  const router = useRouter();

  async function onLogout() {
    await logout();
    router.replace("/login");
  }

  return (
    <ProtectedDashboard>
      <div className="min-h-screen bg-slate-100 text-slate-950 lg:grid lg:grid-cols-[260px_1fr]">
        <aside className="border-r border-slate-200 bg-white px-4 py-5">
          <Link href="/dashboard" className="block font-semibold">
            Abnaa Admin
          </Link>
          <p className="mt-2 text-xs text-slate-500">{user?.name}</p>
          <nav className="mt-6 grid gap-1 text-sm">
            {navItems
              .filter((item) => item.permission === null || hasPermission(item.permission))
              .map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  className="rounded-md px-3 py-2 text-slate-700 hover:bg-slate-100"
                >
                  {item.label}
                </Link>
              ))}
          </nav>
          <button onClick={onLogout} className="mt-6 rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700">
            Logout
          </button>
        </aside>
        <main className="min-w-0 px-4 py-6 sm:px-8">{children}</main>
      </div>
    </ProtectedDashboard>
  );
}

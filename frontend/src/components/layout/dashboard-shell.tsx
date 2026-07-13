"use client";

import { logout } from "@/api/client";
import { ProtectedDashboard, useAuth } from "@/auth/auth-provider";
import Link from "next/link";
import Image from "next/image";
import type { ReactNode } from "react";
import { useRouter } from "next/navigation";
import { LanguageSwitcher } from "@/components/shared/language-switcher";
import { dictionaries, pickLocale, type Dictionary, type Locale } from "@/i18n";
import { useMemo, useState } from "react";

function buildNavItems(dictionary: Dictionary) {
  return [
    { href: "/dashboard", label: dictionary.dashboard.overview, permission: null },
    { href: "/dashboard/business-units", label: dictionary.nav.businessUnits, permission: "business_units.view" },
    { href: "/dashboard/users", label: dictionary.dashboard.users, permission: "users.view" },
    { href: "/dashboard/catalog", label: dictionary.dashboard.catalog, permission: "products.view" },
    { href: "/dashboard/commerce", label: dictionary.dashboard.commerce, permission: "orders.view" },
    { href: "/dashboard/commerce/orders", label: dictionary.dashboard.orders, permission: "orders.view" },
    { href: "/dashboard/wholesale", label: dictionary.nav.wholesale, permission: "wholesale.view" },
    { href: "/dashboard/payments", label: dictionary.dashboard.payments, permission: "payments.view" },
    { href: "/dashboard/inventory", label: dictionary.dashboard.inventory, permission: "inventory.view" },
    { href: "/dashboard/services-rfq", label: dictionary.nav.rfq, permission: "rfq.view" },
    { href: "/dashboard/real-estate", label: dictionary.nav.realEstate, permission: "real_estate.view" },
    { href: "/dashboard/cms", label: dictionary.dashboard.cms, permission: "cms.view" },
    { href: "/dashboard/reports", label: dictionary.dashboard.reports, permission: "reports.view" },
    { href: "/dashboard/settings", label: dictionary.dashboard.settings, permission: "settings.view" },
    { href: "/dashboard/features", label: dictionary.dashboard.features, permission: "settings.view" },
  ];
}

export function DashboardShell({ children }: { children: ReactNode }) {
  const { user, hasPermission } = useAuth();
  const router = useRouter();
  const [locale] = useState<Locale>(() => {
    if (typeof document === "undefined") {
      return "ar";
    }
    return pickLocale(document.cookie.match(/(?:^|; )abu_qasaa_locale=([^;]+)/)?.[1]);
  });
  const dictionary = dictionaries[locale];
  const navItems = useMemo(() => buildNavItems(dictionary), [dictionary]);

  async function onLogout() {
    await logout();
    router.replace("/login");
  }

  return (
    <ProtectedDashboard>
      <div className="aq-dashboard-shell min-h-screen bg-[#eef3f0] text-[var(--aq-ink)] lg:grid lg:grid-cols-[292px_1fr]">
        <aside className="aq-dashboard-sidebar">
          <div className="aq-dashboard-sidebar-head">
            <Link href="/dashboard" className="aq-brand-mark">
              <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={48} height={48} className="aq-logo" />
              <span>
                <span className="block text-sm font-black">{dictionary.common.brandShort}</span>
                <span className="block text-xs text-white/60">{user?.name}</span>
              </span>
            </Link>
            <details className="aq-dashboard-menu">
              <summary>{locale === "ar" ? "التنقل" : "Navigation"}</summary>
              <DashboardNav navItems={navItems} hasPermission={hasPermission} />
            </details>
          </div>
          <div className="aq-dashboard-nav-wrap">
            <DashboardNav navItems={navItems} hasPermission={hasPermission} />
          </div>
          <div className="mt-5 grid gap-2">
            <LanguageSwitcher locale={locale} />
            <button onClick={onLogout} className="rounded-md border border-white/20 px-3 py-2 text-sm font-bold text-white/85 transition hover:bg-white/10">
              {dictionary.dashboard.logout}
            </button>
          </div>
        </aside>
        <section className="min-w-0">
          <header className="sticky top-0 z-20 border-b border-[color:var(--aq-line)] bg-white/88 px-4 py-4 backdrop-blur-xl sm:px-8">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <p className="aq-eyebrow">{dictionary.dashboard.title}</p>
                <p className="text-sm text-[var(--aq-muted)]">{dictionary.dashboard.signedIn} {user?.name ?? "-"}</p>
              </div>
              <Link href="/" className="aq-btn-secondary">
                {dictionary.nav.home}
              </Link>
            </div>
          </header>
          <main className="min-w-0 px-4 py-6 sm:px-8 lg:py-8">{children}</main>
        </section>
      </div>
    </ProtectedDashboard>
  );
}

function DashboardNav({
  navItems,
  hasPermission,
}: {
  navItems: ReturnType<typeof buildNavItems>;
  hasPermission: (permission: string) => boolean;
}) {
  return (
    <nav className="aq-dashboard-nav">
      {navItems
        .filter((item) => item.permission === null || hasPermission(item.permission))
        .map((item) => (
          <Link key={item.href} href={item.href} className="aq-dashboard-nav-link">
            {item.label}
          </Link>
        ))}
    </nav>
  );
}

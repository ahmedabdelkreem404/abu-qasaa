"use client";

import { useAuth } from "@/auth/auth-provider";
import { dictionaries, pickLocale, type Locale } from "@/i18n";
import { useState } from "react";

export default function DashboardPage() {
  const { user } = useAuth();
  const [locale] = useState<Locale>(() => {
    if (typeof document === "undefined") {
      return "ar";
    }
    return pickLocale(document.cookie.match(/(?:^|; )abu_qasaa_locale=([^;]+)/)?.[1]);
  });
  const dictionary = dictionaries[locale];

  const stats = [
    { label: dictionary.nav.businessUnits, value: user?.roles.includes("super_admin") ? "4+" : String(user?.business_units.length ?? 0) },
    { label: "Permissions", value: String(user?.permissions.length ?? 0) },
    { label: dictionary.dashboard.reports, value: "Live" },
  ];

  return (
    <section className="space-y-6">
      <div className="aq-hero p-6 sm:p-8">
        <p className="text-sm font-black text-[var(--aq-gold)]">{dictionary.common.dashboard}</p>
        <h1 className="mt-3 text-4xl font-black text-white sm:text-5xl">{dictionary.dashboard.title}</h1>
        <p className="mt-4 max-w-3xl text-sm leading-8 text-white/76">
          {dictionary.dashboard.signedIn} {user?.name}. Roles: {user?.roles.join(", ") || "none"}.
        </p>
      </div>
      <div className="grid gap-4 md:grid-cols-3">
        {stats.map((stat) => (
          <div key={stat.label} className="aq-card p-5">
            <p className="text-3xl font-black text-[var(--aq-primary)]">{stat.value}</p>
            <p className="mt-1 text-sm font-bold text-[var(--aq-muted)]">{stat.label}</p>
          </div>
        ))}
      </div>
      <div className="aq-card p-5">
        <h2 className="font-black">{dictionary.dashboard.businessUnits}</h2>
        {user?.roles.includes("super_admin") ? (
          <p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{dictionary.dashboard.superAdminAccess}</p>
        ) : (
          <div className="mt-3 grid gap-2">
            {user?.business_units.map((unit) => (
              <div key={unit.id} className="rounded-md bg-[var(--aq-soft)] px-3 py-2 text-sm font-bold">
                {unit.name_en ?? unit.name_ar} · {unit.role}
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  );
}

import Image from "next/image";
import Link from "next/link";
import type { ReactNode } from "react";
import { LanguageSwitcher } from "@/components/shared/language-switcher";
import type { Locale } from "@/i18n";
import type { BusinessUnit } from "@/types/platform";
import { getStorefrontProfile, storefrontName } from "@/storefront/profiles";

export function BusinessStorefrontShell({
  unit,
  locale,
  children,
}: {
  unit: BusinessUnit;
  locale: Locale;
  children: ReactNode;
}) {
  const profile = getStorefrontProfile(unit.slug);
  const name = storefrontName(unit, locale);
  const base = `/${unit.slug}`;

  return (
    <div
      className={`aq-storefront aq-storefront-${unit.slug}`}
      style={{
        "--store-accent": profile.accent,
        "--store-accent-dark": profile.accentDark,
        "--store-surface": profile.surface,
        "--store-text": profile.text,
      } as React.CSSProperties}
    >
      <header className="aq-store-header">
        <nav className="aq-store-container aq-store-nav">
          <Link href={base} className="aq-store-brand">
            <Image src={profile.logo} alt={name} width={68} height={68} className="aq-store-logo" />
            <span>
              <span className="block text-sm font-black">{name}</span>
              <span className="block text-xs font-bold opacity-70">{profile.tagline[locale]}</span>
            </span>
          </Link>
          <div className="aq-store-links">
            {profile.nav.map((item) => (
              <Link key={item.href || "home"} href={item.href ? `${base}/${item.href}` : base} className="aq-store-link">
                {locale === "ar" ? item.ar : item.en}
              </Link>
            ))}
          </div>
          <div className="aq-store-actions">
            <LanguageSwitcher locale={locale} />
            <Link href="/business-units" className="aq-store-pill">{locale === "ar" ? "كل الأنشطة" : "All units"}</Link>
          </div>
        </nav>
        <details className="aq-store-container aq-store-mobile-menu">
          <summary>{locale === "ar" ? "القائمة" : "Menu"}</summary>
          <div>
            {profile.nav.map((item) => (
              <Link key={item.href || "home"} href={item.href ? `${base}/${item.href}` : base}>
                {locale === "ar" ? item.ar : item.en}
              </Link>
            ))}
          </div>
        </details>
      </header>
      <main className="aq-store-container aq-store-main">{children}</main>
      <footer className="aq-store-footer">
        <div className="aq-store-container aq-store-footer-grid">
          <div className="aq-store-brand">
            <Image src={profile.logo} alt={name} width={56} height={56} className="aq-store-logo" />
            <span>
              <span className="block text-sm font-black">{name}</span>
              <span className="block text-xs opacity-70">{profile.promise[locale]}</span>
            </span>
          </div>
          <div className="aq-store-footer-links">
            <Link href="/">{locale === "ar" ? "منصة أبو قصعة" : "Abu Qasaa platform"}</Link>
            <Link href={`${base}/policies`}>{locale === "ar" ? "السياسات" : "Policies"}</Link>
            <Link href="/dashboard">{locale === "ar" ? "لوحة التحكم" : "Dashboard"}</Link>
          </div>
        </div>
      </footer>
    </div>
  );
}

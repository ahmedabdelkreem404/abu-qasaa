import Link from "next/link";
import Image from "next/image";
import { getMenuByLocation } from "@/api/client";
import type { ReactNode } from "react";
import { getDictionary, getLocale } from "@/i18n/server";
import { LanguageSwitcher } from "@/components/shared/language-switcher";

export async function PublicShell({ children }: { children: ReactNode }) {
  const [locale, dictionary] = await Promise.all([getLocale(), getDictionary()]);
  const links = [
    { href: "/", label: dictionary.nav.home },
    { href: "/about", label: dictionary.nav.about },
    { href: "/business-units", label: dictionary.nav.businessUnits },
    { href: "/contact", label: dictionary.nav.contact },
    { href: "/dashboard", label: dictionary.nav.dashboard },
  ];
  const menu = await getMenuByLocation("main").then((response) => response.data).catch(() => null);
  const menuLinks = menu?.items?.filter((item) => item.is_active).map((item) => ({
    href: item.url,
    label: locale === "ar" ? (item.label_ar ?? item.label_en) : (item.label_en ?? item.label_ar),
  })) ?? links;

  return (
    <div className="aq-shell-bg min-h-screen text-[var(--aq-ink)]">
      <header className="aq-public-header">
        <nav className="aq-container aq-header-row">
          <Link href="/" className="aq-brand-mark">
            <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={48} height={48} className="aq-logo" />
            <span className="min-w-0">
              <span className="block text-sm font-black text-[var(--aq-primary-2)] sm:text-base">{dictionary.common.brand}</span>
              <span className="hidden text-xs font-semibold text-[var(--aq-muted)] sm:block">{dictionary.home.eyebrow}</span>
            </span>
          </Link>
          <div className="aq-nav">
            {menuLinks.map((link) => (
              <Link key={link.href} href={link.href} className="aq-nav-link">
                {link.label}
              </Link>
            ))}
          </div>
          <div className="aq-header-actions">
            <LanguageSwitcher locale={locale} />
            <Link href="/dashboard" className="aq-btn-secondary hidden sm:inline-flex">
              {dictionary.common.dashboard}
            </Link>
          </div>
        </nav>
        <details className="aq-container aq-mobile-menu">
          <summary className="aq-menu-summary">{locale === "ar" ? "القائمة" : "Menu"}</summary>
          <div className="aq-mobile-menu-panel">
            {menuLinks.map((link) => (
              <Link key={link.href} href={link.href} className="aq-nav-link">
                {link.label}
              </Link>
            ))}
          </div>
        </details>
      </header>
      <main className="aq-container py-8 sm:py-12">{children}</main>
      <footer className="border-t border-[color:var(--aq-line)] bg-[var(--aq-ink)] text-white">
        <div className="aq-container grid gap-8 py-10 md:grid-cols-[1.2fr_0.8fr]">
          <div className="space-y-4">
            <div className="aq-brand-mark">
              <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={48} height={48} className="aq-logo" />
              <div>
                <p className="font-black">{dictionary.common.brand}</p>
                <p className="text-sm text-white/65">{dictionary.home.body}</p>
              </div>
            </div>
          </div>
          <div className="flex flex-wrap gap-3 text-sm font-bold text-white/80 md:justify-end">
            <Link href="/about">{dictionary.nav.about}</Link>
            <Link href="/business-units">{dictionary.nav.businessUnits}</Link>
            <Link href="/contact">{dictionary.nav.contact}</Link>
          </div>
        </div>
      </footer>
    </div>
  );
}

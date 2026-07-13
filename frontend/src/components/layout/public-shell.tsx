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
      <header className="sticky top-0 z-40 border-b border-[color:var(--aq-line)] bg-white/90 backdrop-blur-xl">
        <nav className="aq-container flex items-center justify-between gap-4 py-3">
          <Link href="/" className="aq-brand-mark">
            <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={48} height={48} className="aq-logo" />
            <span className="min-w-0">
              <span className="block text-sm font-black text-[var(--aq-primary-2)] sm:text-base">{dictionary.common.brand}</span>
              <span className="hidden text-xs font-semibold text-[var(--aq-muted)] sm:block">{dictionary.home.eyebrow}</span>
            </span>
          </Link>
          <div className="hidden items-center gap-1 rounded-full border border-[color:var(--aq-line)] bg-white p-1 text-sm font-bold text-[var(--aq-ink-2)] shadow-sm lg:flex">
            {menuLinks.map((link) => (
              <Link key={link.href} href={link.href} className="rounded-full px-3 py-2 transition hover:bg-[var(--aq-soft)]">
                {link.label}
              </Link>
            ))}
          </div>
          <div className="flex items-center gap-2">
            <LanguageSwitcher locale={locale} />
            <Link href="/dashboard" className="aq-btn-secondary hidden sm:inline-flex">
              {dictionary.common.dashboard}
            </Link>
          </div>
        </nav>
        <div className="aq-container flex gap-2 overflow-x-auto pb-3 text-sm font-bold text-[var(--aq-ink-2)] lg:hidden">
          {menuLinks.map((link) => (
            <Link key={link.href} href={link.href} className="shrink-0 rounded-full border border-[color:var(--aq-line)] bg-white px-3 py-2">
              {link.label}
            </Link>
          ))}
        </div>
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

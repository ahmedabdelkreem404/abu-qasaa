import Link from "next/link";
import { getMenuByLocation } from "@/api/client";
import type { ReactNode } from "react";

const links = [
  { href: "/", label: "Home" },
  { href: "/about", label: "About" },
  { href: "/business-units", label: "Business Units" },
  { href: "/contact", label: "Contact" },
  { href: "/dashboard", label: "Dashboard" },
];

export async function PublicShell({ children }: { children: ReactNode }) {
  const menu = await getMenuByLocation("main").then((response) => response.data).catch(() => null);
  const menuLinks = menu?.items?.filter((item) => item.is_active).map((item) => ({ href: item.url, label: item.label_en ?? item.label_ar })) ?? links;

  return (
    <div className="min-h-screen bg-slate-50 text-slate-950">
      <header className="border-b border-slate-200 bg-white">
        <nav className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
          <Link href="/" className="font-semibold text-teal-800">
            Abnaa Abu Qasaa Trading
          </Link>
          <div className="flex flex-wrap items-center gap-4 text-sm text-slate-600">
            {menuLinks.map((link) => (
              <Link key={link.href} href={link.href}>
                {link.label}
              </Link>
            ))}
          </div>
        </nav>
      </header>
      <main className="mx-auto w-full max-w-6xl px-4 py-10">{children}</main>
      <footer className="border-t border-slate-200 bg-white">
        <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-6 text-sm text-slate-600">
          <span>Abnaa Abu Qasaa Trading</span>
          <div className="flex gap-4">
            <Link href="/about">About</Link>
            <Link href="/business-units">Business Units</Link>
            <Link href="/contact">Contact</Link>
          </div>
        </div>
      </footer>
    </div>
  );
}

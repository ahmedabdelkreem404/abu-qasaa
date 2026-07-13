"use client";

import { useAuth } from "@/auth/auth-provider";
import { dictionaries, pickLocale, type Locale } from "@/i18n";
import Image from "next/image";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

export default function LoginPage() {
  const { login } = useAuth();
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [locale] = useState<Locale>(() => {
    if (typeof document === "undefined") {
      return "ar";
    }
    return pickLocale(document.cookie.match(/(?:^|; )abu_qasaa_locale=([^;]+)/)?.[1]);
  });
  const dictionary = dictionaries[locale];

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setError(null);
    setIsLoading(true);

    try {
      await login(String(form.get("email")), String(form.get("password")));
      const next = new URLSearchParams(window.location.search).get("next");
      router.replace(next ?? "/dashboard");
    } catch {
      setError(dictionary.login.error);
    } finally {
      setIsLoading(false);
    }
  }

  return (
    <main className="aq-shell-bg grid min-h-screen items-center px-4 py-8 lg:grid-cols-[1fr_520px] lg:px-10">
      <section className="hidden max-w-4xl space-y-6 lg:block">
        <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={96} height={96} priority className="h-24 w-24 rounded-md bg-white object-contain p-2 shadow-xl" />
        <div>
          <p className="aq-eyebrow">{dictionary.home.eyebrow}</p>
          <h1 className="aq-display mt-3 max-w-4xl">{dictionary.dashboard.title}</h1>
          <p className="aq-subtitle mt-5 max-w-2xl">{dictionary.dashboard.subtitle}</p>
        </div>
      </section>
      <form onSubmit={onSubmit} className="aq-card mx-auto grid w-full max-w-md gap-5 p-6">
        <div className="space-y-2">
          <Image src="/brand/abu-qasaa-oils-logo.jpg" alt="Abu Qasaa Oils logo" width={64} height={64} className="h-16 w-16 rounded-md bg-white object-contain p-1 shadow lg:hidden" />
          <p className="aq-eyebrow">{dictionary.common.dashboard}</p>
          <h1 className="text-3xl font-black">{dictionary.login.title}</h1>
          <p className="text-sm leading-7 text-[var(--aq-muted)]">{dictionary.login.subtitle}</p>
        </div>
        {error ? <p className="rounded-md bg-red-50 p-3 text-sm font-bold text-red-700">{error}</p> : null}
        <label className="grid gap-1 text-sm font-bold text-[var(--aq-ink-2)]">
          {dictionary.login.email}
          <input name="email" type="email" required className="px-3 py-2.5 font-normal" />
        </label>
        <label className="grid gap-1 text-sm font-bold text-[var(--aq-ink-2)]">
          {dictionary.login.password}
          <input name="password" type="password" required className="px-3 py-2.5 font-normal" />
        </label>
        <button disabled={isLoading} className="aq-btn disabled:opacity-60">
          {isLoading ? dictionary.login.loading : dictionary.login.submit}
        </button>
      </form>
    </main>
  );
}

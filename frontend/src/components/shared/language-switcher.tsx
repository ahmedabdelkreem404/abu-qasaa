"use client";

import { useRouter } from "next/navigation";
import type { Locale } from "@/i18n";

export function LanguageSwitcher({ locale }: { locale: Locale }) {
  const router = useRouter();
  const nextLocale: Locale = locale === "ar" ? "en" : "ar";

  function switchLocale() {
    document.cookie = `abu_qasaa_locale=${nextLocale}; path=/; max-age=31536000; samesite=lax`;
    document.documentElement.lang = nextLocale;
    document.documentElement.dir = nextLocale === "ar" ? "rtl" : "ltr";
    router.refresh();
  }

  return (
    <button
      type="button"
      onClick={switchLocale}
      className="aq-locale-switch"
      aria-label={nextLocale === "ar" ? "Switch to Arabic" : "Switch to English"}
    >
      {nextLocale === "ar" ? "العربية" : "English"}
    </button>
  );
}

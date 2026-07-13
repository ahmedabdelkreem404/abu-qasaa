import ar from "./messages/ar.json";
import en from "./messages/en.json";

export const locales = ["ar", "en"] as const;
export type Locale = (typeof locales)[number];
export type Dictionary = typeof en;

export const defaultLocale: Locale = "ar";

export const dictionaries: Record<Locale, Dictionary> = {
  ar,
  en,
};

export function isLocale(value: string | undefined | null): value is Locale {
  return value === "ar" || value === "en";
}

export function getDirection(locale: Locale) {
  return locale === "ar" ? "rtl" : "ltr";
}

export function pickLocale(value: string | undefined | null): Locale {
  return isLocale(value) ? value : defaultLocale;
}

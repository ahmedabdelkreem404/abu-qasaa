import type { Locale } from "@/i18n";
import type { BusinessUnit, Product } from "@/types/platform";

export type StorefrontProfile = {
  slug: string;
  logo: string;
  heroImage: string;
  gallery: string[];
  accent: string;
  accentDark: string;
  surface: string;
  text: string;
  nav: Array<{ href: string; ar: string; en: string }>;
  tagline: { ar: string; en: string };
  promise: { ar: string; en: string };
  policies: Array<{ titleAr: string; titleEn: string; bodyAr: string; bodyEn: string }>;
  productFallbacks: string[];
};

export const storefrontProfiles: Record<string, StorefrontProfile> = {
  dates: {
    slug: "dates",
    logo: "/brand/ghosoun/logo.jpeg",
    heroImage: "/brand/ghosoun/hero-dates.jpg",
    gallery: [
      "/brand/ghosoun/hero-dates.jpg",
      "/brand/ghosoun/gift-box.jpg",
      "/brand/ghosoun/assortment.jpg",
      "/brand/ghosoun/premium-box.jpg",
      "/brand/ghosoun/farm-harvest.jpg",
      "/brand/ghosoun/date-molasses.jpg",
    ],
    accent: "#a7652c",
    accentDark: "#4b2413",
    surface: "#fbf2e4",
    text: "#2f1b12",
    nav: [
      { href: "", ar: "الرئيسية", en: "Home" },
      { href: "products", ar: "المنتجات", en: "Products" },
      { href: "gift-boxes", ar: "علب الهدايا", en: "Gift boxes" },
      { href: "corporate-gifts", ar: "هدايا الشركات", en: "Corporate gifts" },
      { href: "gallery", ar: "المعرض", en: "Gallery" },
      { href: "policies", ar: "السياسات", en: "Policies" },
    ],
    tagline: {
      ar: "غصون للتمور",
      en: "Ghosoun for Dates",
    },
    promise: {
      ar: "تمور وهدايا فاخرة بتجربة دافئة، راقية، ومناسبة للبيت والضيافة والشركات.",
      en: "Premium dates and gifts with a warm, refined experience for homes, hospitality, and companies.",
    },
    policies: [
      {
        titleAr: "سياسة الجودة",
        titleEn: "Quality policy",
        bodyAr: "نختار المنتجات بعناية ونراجع التغليف قبل التسليم لضمان تجربة تليق بالضيافة.",
        bodyEn: "Products are selected carefully and packaging is reviewed before delivery.",
      },
      {
        titleAr: "سياسة الهدايا",
        titleEn: "Gifting policy",
        bodyAr: "يمكن تخصيص الهدايا المؤسسية حسب الكمية والمناسبة وبيانات الجهة.",
        bodyEn: "Corporate gifts can be customized by quantity, occasion, and brand details.",
      },
      {
        titleAr: "سياسة التوصيل",
        titleEn: "Delivery policy",
        bodyAr: "يتم تأكيد الطلب وموعد التسليم قبل التجهيز، وتظهر تفاصيل الدفع من صفحة الطلب.",
        bodyEn: "Orders and delivery windows are confirmed before preparation.",
      },
    ],
    productFallbacks: [
      "/brand/ghosoun/gift-box.jpg",
      "/brand/ghosoun/assortment.jpg",
      "/brand/ghosoun/premium-box.jpg",
      "/brand/ghosoun/date-molasses.jpg",
    ],
  },
  oils: {
    slug: "oils",
    logo: "/brand/abu-qasaa-oils-logo.jpg",
    heroImage: "/brand/abu-qasaa-oils-logo.jpg",
    gallery: ["/brand/abu-qasaa-oils-logo.jpg"],
    accent: "#1f6f4a",
    accentDark: "#17201d",
    surface: "#eef5f1",
    text: "#17201d",
    nav: [
      { href: "", ar: "الرئيسية", en: "Home" },
      { href: "products", ar: "المنتجات", en: "Products" },
      { href: "wholesale", ar: "الجملة", en: "Wholesale" },
      { href: "gallery", ar: "المعرض", en: "Gallery" },
      { href: "policies", ar: "السياسات", en: "Policies" },
    ],
    tagline: { ar: "أبو قصعة للزيوت", en: "Abu Qasaa Oils" },
    promise: {
      ar: "واجهة توريد واضحة للزيوت والشحوم الصناعية والبيع بالجملة.",
      en: "A clear supply storefront for industrial oils, lubricants, and wholesale.",
    },
    policies: [
      {
        titleAr: "سياسة أسعار الجملة",
        titleEn: "Wholesale pricing",
        bodyAr: "أسعار الجملة تظهر فقط للعملاء المعتمدين وبحسب قائمة الأسعار المخصصة.",
        bodyEn: "Wholesale prices are shown only to approved customers with assigned price lists.",
      },
    ],
    productFallbacks: ["/brand/abu-qasaa-oils-logo.jpg"],
  },
  "real-estate": {
    slug: "real-estate",
    logo: "/brand/abu-qasaa-oils-logo.jpg",
    heroImage: "/brand/abu-qasaa-oils-logo.jpg",
    gallery: ["/brand/abu-qasaa-oils-logo.jpg"],
    accent: "#32455f",
    accentDark: "#172238",
    surface: "#eef2f6",
    text: "#172238",
    nav: [
      { href: "", ar: "الرئيسية", en: "Home" },
      { href: "real-estate", ar: "المشروعات", en: "Projects" },
      { href: "gallery", ar: "المعرض", en: "Gallery" },
      { href: "policies", ar: "السياسات", en: "Policies" },
    ],
    tagline: { ar: "أبو قصعة للعقارات", en: "Abu Qasaa Real Estate" },
    promise: {
      ar: "مشروعات ووحدات وطلبات معاينة داخل تجربة عقارية منفصلة.",
      en: "Projects, units, and viewing requests in a dedicated real estate experience.",
    },
    policies: [
      {
        titleAr: "سياسة الحجز",
        titleEn: "Reservation policy",
        bodyAr: "طلبات الاهتمام لا تعد حجزا نهائيا إلا بعد مراجعة الإدارة وتأكيد التوفر.",
        bodyEn: "Reservation interest is confirmed only after admin review and availability checks.",
      },
    ],
    productFallbacks: ["/brand/abu-qasaa-oils-logo.jpg"],
  },
  "import-export": {
    slug: "import-export",
    logo: "/brand/abu-qasaa-oils-logo.jpg",
    heroImage: "/brand/abu-qasaa-oils-logo.jpg",
    gallery: ["/brand/abu-qasaa-oils-logo.jpg"],
    accent: "#176b87",
    accentDark: "#0e3546",
    surface: "#eef7f8",
    text: "#0e3546",
    nav: [
      { href: "", ar: "الرئيسية", en: "Home" },
      { href: "services", ar: "الخدمات", en: "Services" },
      { href: "rfq", ar: "طلب عرض سعر", en: "RFQ" },
      { href: "gallery", ar: "المعرض", en: "Gallery" },
      { href: "policies", ar: "السياسات", en: "Policies" },
    ],
    tagline: { ar: "أبو قصعة للاستيراد والتصدير", en: "Abu Qasaa Import & Export" },
    promise: {
      ar: "خدمات تجارة وشحن وجمارك وطلبات أسعار بواجهة مستقلة.",
      en: "Trade, shipping, customs, and RFQ services in a dedicated storefront.",
    },
    policies: [
      {
        titleAr: "سياسة عروض الأسعار",
        titleEn: "RFQ policy",
        bodyAr: "يتم تسعير الخدمات بعد مراجعة بيانات الشحنة والمسار والمستندات المطلوبة.",
        bodyEn: "Quotations are prepared after reviewing cargo, route, and required documents.",
      },
    ],
    productFallbacks: ["/brand/abu-qasaa-oils-logo.jpg"],
  },
};

export function getStorefrontProfile(slug: string) {
  return storefrontProfiles[slug] ?? storefrontProfiles.dates;
}

export function localized(locale: Locale, ar?: string | null, en?: string | null) {
  return locale === "ar" ? (ar ?? en ?? "") : (en ?? ar ?? "");
}

export function storefrontName(unit: BusinessUnit, locale: Locale) {
  return localized(locale, unit.name_ar, unit.name_en);
}

export function productName(product: Product, locale: Locale) {
  return localized(locale, product.name_ar, product.name_en);
}

export function productSummary(product: Product, locale: Locale) {
  return localized(
    locale,
    product.short_description_ar ?? product.category?.name_ar,
    product.short_description_en ?? product.category?.name_en,
  );
}

export function productImage(product: Product, profile: StorefrontProfile, index = 0) {
  return product.featured_image ?? product.images?.find((image) => image.is_primary)?.image ?? profile.productFallbacks[index % profile.productFallbacks.length];
}

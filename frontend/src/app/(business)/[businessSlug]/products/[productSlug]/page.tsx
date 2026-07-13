import { getPublicProductAvailability, getPublicProductBySlug } from "@/api/client";
import { AddToCartButton } from "@/commerce/cart-tools";
import { ApiErrorState } from "@/components/shared/api-state";
import { getDictionary, getLocale } from "@/i18n/server";
import { getStorefrontProfile, localized, productImage, productName, productSummary } from "@/storefront/profiles";
import Image from "next/image";

export default async function ProductDetailPage({
  params,
}: {
  params: Promise<{ businessSlug: string; productSlug: string }>;
}) {
  const { businessSlug, productSlug } = await params;
  const [product, availability, locale, dictionary] = await Promise.all([
    getPublicProductBySlug(businessSlug, productSlug).then((response) => response.data).catch(() => null),
    getPublicProductAvailability(businessSlug, productSlug).then((response) => response.data).catch(() => null),
    getLocale(),
    getDictionary(),
  ]);

  if (!product) {
    return <ApiErrorState message="Product is not available." />;
  }

  const profile = getStorefrontProfile(businessSlug);
  const title = productName(product, locale);

  return (
    <section className="grid gap-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
      <div className="aq-store-product-image min-h-[30rem] rounded-[var(--aq-radius)] shadow-[var(--aq-shadow)]">
        <Image src={productImage(product, profile)} alt={title} fill priority sizes="(max-width: 1023px) 100vw, 45vw" />
      </div>
      <div className="space-y-6">
        <div>
          <p className="aq-store-kicker">{localized(locale, product.category?.name_ar, product.category?.name_en) || dictionary.public.productsTitle}</p>
          <div className="mt-3 flex flex-wrap gap-2">
            {product.badges?.map((badge) => <span key={badge.id} className="aq-chip">{localized(locale, badge.name_ar, badge.name_en)}</span>)}
            {product.bundle ? <span className="aq-chip">{localized(locale, product.bundle.name_ar, product.bundle.name_en)}</span> : null}
          </div>
          <h1 className="aq-store-title mt-2">{title}</h1>
          <p className="aq-subtitle mt-3">{productSummary(product, locale)}</p>
          {product.base_price ? <p className="mt-4 text-2xl font-black" style={{ color: "var(--store-accent)" }}>{product.base_price} {product.currency}</p> : null}
          {availability ? <p className={`mt-3 text-sm font-bold ${availability.in_stock ? "text-emerald-700" : "text-amber-700"}`}>{availability.in_stock ? (locale === "ar" ? "متوفر" : "In stock") : (locale === "ar" ? "غير متوفر" : "Out of stock")}{availability.available_quantity !== null && availability.available_quantity !== undefined ? ` · ${availability.available_quantity}` : ""}</p> : null}
        </div>
        <div className="aq-card p-4">
          <label className="grid max-w-32 gap-1 text-sm font-bold">{locale === "ar" ? "الكمية" : "Quantity"}<input type="number" min={1} defaultValue={1} className="px-3 py-2 font-normal" /></label>
          <AddToCartButton businessSlug={businessSlug} product={product} disabled={availability?.inventory_enabled === true && !availability.in_stock} />
          <p className="mt-2 text-sm text-slate-500">{locale === "ar" ? "يتم تأكيد الطلب والدفع من خلال صفحة الطلب." : "Orders and payment are confirmed from the order page."}</p>
        </div>
        <Info title={locale === "ar" ? "الوصف" : "Description"} value={localized(locale, product.description_ar, product.description_en) || productSummary(product, locale) || dictionary.common.noData} />
        <Info title={dictionary.common.brandLabel} value={localized(locale, product.brand?.name_ar, product.brand?.name_en) || "-"} />
        {product.bundle ? <Info title={locale === "ar" ? "الباقة" : "Bundle"} value={`${localized(locale, product.bundle.name_ar, product.bundle.name_en)} · ${product.bundle.bundle_type.replaceAll("_", " ")}`} /> : null}
        <div className="aq-card p-5">
          <h2 className="font-semibold">{locale === "ar" ? "المواصفات" : "Specs"}</h2>
          <dl className="mt-3 grid gap-2 text-sm">
            {Object.entries(product.specs_json ?? {}).map(([key, value]) => <div key={key} className="flex justify-between gap-4"><dt className="text-slate-500">{key}</dt><dd>{String(value)}</dd></div>)}
          </dl>
        </div>
        <div className="aq-card p-5">
          <h2 className="font-semibold">{locale === "ar" ? "الاختيارات" : "Variants"}</h2>
          {(product.variants ?? []).length === 0 ? <p className="mt-2 text-sm text-slate-600">{locale === "ar" ? "لا توجد اختيارات." : "No variants."}</p> : <ul className="mt-3 grid gap-2 text-sm">{product.variants?.map((variant) => <li key={variant.id}>{localized(locale, variant.name_ar, variant.name_en) || variant.sku}</li>)}</ul>}
        </div>
      </div>
    </section>
  );
}

function Info({ title, value }: { title: string; value: string }) {
  return <div className="aq-card p-5"><h2 className="font-black">{title}</h2><p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{value}</p></div>;
}

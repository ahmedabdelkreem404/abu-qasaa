import Image from "next/image";
import Link from "next/link";
import { AddToCartButton } from "@/commerce/cart-tools";
import type { Locale } from "@/i18n";
import type { Product, ProductCollection } from "@/types/platform";
import { productImage, productName, productSummary, type StorefrontProfile, localized } from "@/storefront/profiles";

export function StorefrontGallery({ images, title }: { images: string[]; title: string }) {
  return (
    <div className="aq-store-gallery">
      {images.map((image, index) => (
        <div key={`${image}-${index}`} className="aq-store-gallery-item">
          <Image src={image} alt={`${title} ${index + 1}`} fill sizes="(max-width: 1023px) 100vw, 50vw" />
        </div>
      ))}
    </div>
  );
}

export function StorefrontProductGrid({
  businessSlug,
  products,
  empty,
  locale,
  profile,
}: {
  businessSlug: string;
  products: Product[];
  empty: string;
  locale: Locale;
  profile: StorefrontProfile;
}) {
  if (products.length === 0) {
    return <p className="aq-store-policy text-sm font-bold">{empty}</p>;
  }

  return (
    <div className="aq-store-product-grid">
      {products.map((product, index) => (
        <article key={product.id} className="aq-store-product-card">
          <Link href={`/${businessSlug}/products/${product.slug}`}>
            <div className="aq-store-product-image">
              <Image src={productImage(product, profile, index)} alt={productName(product, locale)} fill sizes="(max-width: 768px) 100vw, 33vw" />
            </div>
            <div className="aq-store-product-body">
              <div className="flex flex-wrap gap-2">
                {product.badges?.slice(0, 3).map((badge) => (
                  <span key={badge.id} className="aq-chip">{localized(locale, badge.name_ar, badge.name_en)}</span>
                ))}
                {product.bundle ? <span className="aq-chip">{localized(locale, product.bundle.name_ar, product.bundle.name_en)}</span> : null}
              </div>
              <h2 className="text-lg font-black">{productName(product, locale)}</h2>
              <p className="line-clamp-2 text-sm leading-7 text-[var(--aq-muted)]">{productSummary(product, locale)}</p>
              {product.base_price ? <p className="text-lg font-black" style={{ color: "var(--store-accent)" }}>{product.base_price} {product.currency}</p> : null}
            </div>
          </Link>
          <div className="px-4 pb-4">
            <AddToCartButton businessSlug={businessSlug} product={product} />
          </div>
        </article>
      ))}
    </div>
  );
}

export function StorefrontCollectionGrid({
  businessSlug,
  collections,
  locale,
  profile,
}: {
  businessSlug: string;
  collections: ProductCollection[];
  locale: Locale;
  profile: StorefrontProfile;
}) {
  if (collections.length === 0) {
    return <p className="aq-store-policy text-sm font-bold">{locale === "ar" ? "لا توجد مجموعات متاحة حاليا." : "No collections are available yet."}</p>;
  }

  return (
    <div className="aq-store-product-grid">
      {collections.map((collection, index) => (
        <Link key={collection.id} href={`/${businessSlug}/collections/${collection.slug}`} className="aq-store-product-card">
          <div className="aq-store-product-image">
            <Image src={collection.image ?? profile.gallery[index % profile.gallery.length]} alt={localized(locale, collection.name_ar, collection.name_en)} fill sizes="(max-width: 768px) 100vw, 33vw" />
          </div>
          <div className="aq-store-product-body">
            <h2 className="text-lg font-black">{localized(locale, collection.name_ar, collection.name_en)}</h2>
            <p className="line-clamp-2 text-sm leading-7 text-[var(--aq-muted)]">
              {localized(locale, collection.description_ar, collection.description_en) || (locale === "ar" ? "اختيارات منتقاة بعناية." : "Carefully curated selections.")}
            </p>
          </div>
        </Link>
      ))}
    </div>
  );
}

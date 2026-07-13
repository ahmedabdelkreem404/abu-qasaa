import { getPublicBusinessUnitBySlug } from "@/api/client";
import { BusinessStorefrontShell } from "@/components/layout/business-storefront-shell";
import { ApiErrorState } from "@/components/shared/api-state";
import { getDictionary, getLocale } from "@/i18n/server";
import type { ReactNode } from "react";

export default async function BusinessUnitLayout({
  children,
  params,
}: {
  children: ReactNode;
  params: Promise<{ businessSlug: string }>;
}) {
  const [{ businessSlug }, locale, dictionary] = await Promise.all([params, getLocale(), getDictionary()]);
  const unit = await getPublicBusinessUnitBySlug(businessSlug).then((response) => response.data).catch(() => null);

  if (!unit) {
    return (
      <main className="aq-container py-8">
        <ApiErrorState message={dictionary.common.noData} />
      </main>
    );
  }

  return <BusinessStorefrontShell unit={unit} locale={locale}>{children}</BusinessStorefrontShell>;
}

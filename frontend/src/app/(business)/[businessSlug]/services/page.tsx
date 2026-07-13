import { listPublicServices } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import { getDictionary } from "@/i18n/server";
import Link from "next/link";

export default async function ServicesPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const [services, dictionary] = await Promise.all([
    listPublicServices(businessSlug).then((response) => response.data).catch(() => null),
    getDictionary(),
  ]);

  if (services === null) {
    return <ApiErrorState message="Services are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <p className="aq-eyebrow">{businessSlug}</p>
        <h1 className="aq-title">{dictionary.public.servicesTitle}</h1>
        <p className="aq-subtitle mt-2 max-w-2xl">{dictionary.public.servicesBody}</p>
      </div>
      {services.length === 0 ? <EmptyState message="No services yet." /> : <div className="aq-grid-auto">{services.map((service) => <Link key={service.id} href={`/${businessSlug}/services/${service.slug}`} className="aq-card p-5 transition hover:-translate-y-1"><span className="aq-chip">{service.category}</span><h2 className="mt-4 text-xl font-black">{service.name_en ?? service.name_ar}</h2><p className="mt-2 text-sm leading-7 text-[var(--aq-muted)]">{service.summary_en ?? service.summary_ar ?? service.category}</p></Link>)}</div>}
      <Link href={`/${businessSlug}/rfq`} className="aq-btn">{dictionary.common.requestQuote}</Link>
    </section>
  );
}

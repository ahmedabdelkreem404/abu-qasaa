import { listPublicServices } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import Link from "next/link";

export default async function ServicesPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;
  const services = await listPublicServices(businessSlug).then((response) => response.data).catch(() => null);

  if (services === null) {
    return <ApiErrorState message="Services are not available." />;
  }

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold">Services</h1>
        <p className="mt-2 max-w-2xl text-slate-600">Import, export, shipping, and customs RFQ services.</p>
      </div>
      {services.length === 0 ? <EmptyState message="No services yet." /> : <div className="grid gap-4 md:grid-cols-3">{services.map((service) => <Link key={service.id} href={`/${businessSlug}/services/${service.slug}`} className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">{service.name_en ?? service.name_ar}</h2><p className="mt-2 text-sm text-slate-600">{service.summary_en ?? service.summary_ar ?? service.category}</p></Link>)}</div>}
      <Link href={`/${businessSlug}/rfq`} className="inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Request quotation</Link>
    </section>
  );
}

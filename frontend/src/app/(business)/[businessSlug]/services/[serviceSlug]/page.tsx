import { getPublicService } from "@/api/client";
import { ApiErrorState } from "@/components/shared/api-state";
import Link from "next/link";

export default async function ServiceDetailPage({ params }: { params: Promise<{ businessSlug: string; serviceSlug: string }> }) {
  const { businessSlug, serviceSlug } = await params;
  const service = await getPublicService(businessSlug, serviceSlug).then((response) => response.data).catch(() => null);

  if (!service) {
    return <ApiErrorState message="Service is not available." />;
  }

  return <section className="space-y-6"><h1 className="text-3xl font-semibold">{service.name_en ?? service.name_ar}</h1><p className="max-w-2xl text-slate-600">{service.description_en ?? service.description_ar ?? service.summary_en ?? service.summary_ar}</p><Link href={`/${businessSlug}/rfq`} className="inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">Request quotation</Link></section>;
}

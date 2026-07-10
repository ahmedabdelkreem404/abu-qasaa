import { PublicPaymentPage } from "@/commerce/payment-page";

export default async function PaymentPage({
  params,
  searchParams,
}: {
  params: Promise<{ businessSlug: string; orderNumber: string }>;
  searchParams: Promise<{ phone?: string }>;
}) {
  const { businessSlug, orderNumber } = await params;
  const { phone } = await searchParams;

  return <PublicPaymentPage businessSlug={businessSlug} orderNumber={orderNumber} initialPhone={phone} />;
}

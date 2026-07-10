import { PaymentMethodForm } from "@/payments/method-form";

export default async function EditPaymentMethodPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <section className="space-y-6"><h1 className="text-2xl font-semibold">Edit payment method</h1><PaymentMethodForm id={id} /></section>;
}

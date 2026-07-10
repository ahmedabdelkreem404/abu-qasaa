import { CheckoutForm } from "@/commerce/checkout-form";

export default async function CheckoutPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;

  return <section className="space-y-6"><h1 className="text-3xl font-semibold">Checkout</h1><CheckoutForm businessSlug={businessSlug} /></section>;
}

import { CheckoutForm } from "@/commerce/checkout-form";

export default async function CheckoutPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;

  return <section className="space-y-6"><div><p className="aq-eyebrow">{businessSlug}</p><h1 className="aq-title">Checkout</h1></div><CheckoutForm businessSlug={businessSlug} /></section>;
}

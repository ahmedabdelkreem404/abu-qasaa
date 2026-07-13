import { CartManager } from "@/commerce/cart-tools";

export default async function CartPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;

  return <section className="space-y-6"><div><p className="aq-eyebrow">{businessSlug}</p><h1 className="aq-title">Cart</h1></div><CartManager businessSlug={businessSlug} /></section>;
}

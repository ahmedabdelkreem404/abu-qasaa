import { CartManager } from "@/commerce/cart-tools";

export default async function CartPage({ params }: { params: Promise<{ businessSlug: string }> }) {
  const { businessSlug } = await params;

  return <section className="space-y-6"><h1 className="text-3xl font-semibold">Cart</h1><CartManager businessSlug={businessSlug} /></section>;
}

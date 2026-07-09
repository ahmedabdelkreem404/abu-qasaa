import { PlaceholderPage } from "@/components/shared/placeholder-page";

export default async function BusinessHomePage({
  params,
}: {
  params: Promise<{ businessSlug: string }>;
}) {
  const { businessSlug } = await params;

  return (
    <PlaceholderPage
      title={businessSlug}
      description="Dynamic business unit homepage placeholder. Content, modules, and navigation will be resolved from the business unit API."
    />
  );
}

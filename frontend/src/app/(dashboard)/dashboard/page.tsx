import { PlaceholderPage } from "@/components/shared/placeholder-page";

export default function DashboardPage() {
  return (
    <PlaceholderPage
      title="Dashboard"
      description="Operational overview placeholder for super admins and business-unit managers."
      items={["Business unit health", "Pending RFQs", "Recent orders", "Open leads"]}
    />
  );
}

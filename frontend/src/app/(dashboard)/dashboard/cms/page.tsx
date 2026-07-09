import Link from "next/link";

export default function DashboardCmsPage() {
  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">CMS</h1>
      <div className="grid gap-4 md:grid-cols-2">
        <Link href="/dashboard/cms/pages" className="rounded-md border border-slate-200 bg-white p-5">Manage pages</Link>
        <Link href="/dashboard/cms/contact-inquiries" className="rounded-md border border-slate-200 bg-white p-5">Contact inquiries</Link>
      </div>
    </section>
  );
}

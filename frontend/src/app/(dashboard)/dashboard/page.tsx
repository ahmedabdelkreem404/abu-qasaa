"use client";

import { useAuth } from "@/auth/auth-provider";

export default function DashboardPage() {
  const { user } = useAuth();

  return (
    <section className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold">Dashboard</h1>
        <p className="text-sm text-slate-600">
          Signed in as {user?.name}. Roles: {user?.roles.join(", ") || "none"}.
        </p>
      </div>
      <div className="rounded-md border border-slate-200 bg-white p-5">
        <h2 className="font-medium">Accessible Business Units</h2>
        {user?.roles.includes("super_admin") ? (
          <p className="mt-2 text-sm text-slate-600">Super Admin can access all business units.</p>
        ) : (
          <div className="mt-3 grid gap-2">
            {user?.business_units.map((unit) => (
              <div key={unit.id} className="rounded-md bg-slate-50 px-3 py-2 text-sm">
                {unit.name_en ?? unit.name_ar} · {unit.role}
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  );
}

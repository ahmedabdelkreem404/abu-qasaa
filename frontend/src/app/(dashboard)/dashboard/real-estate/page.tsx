"use client";

import { listRealEstateLeads, listRealEstateProjects } from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { RealEstateLead, RealEstateProject } from "@/types/platform";
import { useEffect, useState } from "react";

export default function DashboardRealEstatePage() {
  const [projects, setProjects] = useState<RealEstateProject[] | null>(null);
  const [leads, setLeads] = useState<RealEstateLead[]>([]);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([listRealEstateProjects(), listRealEstateLeads()])
      .then(([projectResponse, leadResponse]) => { setProjects(projectResponse.data); setLeads(leadResponse.data); })
      .catch((event) => setError(event instanceof Error && event.name === "403" ? "Forbidden." : "Could not load real estate."));
  }, []);

  if (error) return <ApiErrorState message={error} />;
  if (!projects) return <p className="text-sm text-slate-600">Loading real estate...</p>;

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-semibold">Real Estate</h1>
      {projects.length === 0 ? <EmptyState message="No projects yet." /> : <Panel title="Projects" rows={projects.map((project) => [project.project_code, project.name_en ?? project.name_ar, project.status])} />}
      <Panel title="Leads" rows={leads.map((lead) => [lead.name, lead.phone, lead.status])} />
    </section>
  );
}

function Panel({ title, rows }: { title: string; rows: string[][] }) {
  return <div className="rounded-md border border-slate-200 bg-white p-5"><h2 className="font-semibold">{title}</h2><table className="mt-4 w-full text-left text-sm"><tbody>{rows.map((row, index) => <tr key={index} className="border-t border-slate-100 first:border-t-0">{row.map((cell, cellIndex) => <td key={cellIndex} className="p-3">{cell}</td>)}</tr>)}</tbody></table></div>;
}

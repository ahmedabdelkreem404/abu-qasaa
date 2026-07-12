import Link from "next/link";

export default function NotFound() {
  return (
    <main className="mx-auto flex min-h-screen w-full max-w-3xl flex-col justify-center px-6 py-16">
      <p className="text-sm font-semibold uppercase tracking-wide text-emerald-700">
        404
      </p>
      <h1 className="mt-3 text-3xl font-semibold text-slate-950">
        Page not found
      </h1>
      <p className="mt-4 max-w-xl text-sm leading-6 text-slate-600">
        The page may have moved, or the selected business unit does not expose
        this module.
      </p>
      <div className="mt-8 flex flex-wrap gap-3">
        <Link className="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white" href="/">
          Home
        </Link>
        <Link className="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-800" href="/dashboard">
          Dashboard
        </Link>
      </div>
    </main>
  );
}

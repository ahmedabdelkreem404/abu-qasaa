"use client";

import { useAuth } from "@/auth/auth-provider";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

export default function LoginPage() {
  const { login } = useAuth();
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setError(null);
    setIsLoading(true);

    try {
      await login(String(form.get("email")), String(form.get("password")));
      const next = new URLSearchParams(window.location.search).get("next");
      router.replace(next ?? "/dashboard");
    } catch {
      setError("Invalid credentials or inactive user.");
    } finally {
      setIsLoading(false);
    }
  }

  return (
    <main className="flex min-h-screen items-center justify-center bg-slate-100 px-4">
      <form onSubmit={onSubmit} className="grid w-full max-w-sm gap-4 rounded-md border border-slate-200 bg-white p-6">
        <div>
          <h1 className="text-2xl font-semibold">Dashboard Login</h1>
          <p className="mt-1 text-sm text-slate-600">Use your assigned platform account.</p>
        </div>
        {error ? <p className="rounded-md bg-red-50 p-3 text-sm text-red-700">{error}</p> : null}
        <label className="grid gap-1 text-sm">
          Email
          <input name="email" type="email" required className="rounded-md border border-slate-300 px-3 py-2" />
        </label>
        <label className="grid gap-1 text-sm">
          Password
          <input name="password" type="password" required className="rounded-md border border-slate-300 px-3 py-2" />
        </label>
        <button disabled={isLoading} className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">
          {isLoading ? "Signing in..." : "Sign in"}
        </button>
      </form>
    </main>
  );
}

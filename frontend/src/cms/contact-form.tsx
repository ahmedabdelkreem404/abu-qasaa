"use client";

import { submitContactInquiry } from "@/api/client";
import { FormEvent, useState } from "react";

export function ContactForm() {
  const [status, setStatus] = useState<"idle" | "loading" | "success" | "error">("idle");

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setStatus("loading");

    try {
      await submitContactInquiry({
        name: String(form.get("name") ?? ""),
        email: String(form.get("email") ?? "") || undefined,
        phone: String(form.get("phone") ?? "") || undefined,
        subject: String(form.get("subject") ?? "") || undefined,
        message: String(form.get("message") ?? ""),
        source_page: "/contact",
      });
      event.currentTarget.reset();
      setStatus("success");
    } catch {
      setStatus("error");
    }
  }

  return (
    <form onSubmit={onSubmit} className="grid gap-4 rounded-md border border-slate-200 bg-white p-5">
      {status === "success" ? <p className="text-sm text-teal-700">Inquiry sent successfully.</p> : null}
      {status === "error" ? <p className="text-sm text-red-600">Could not send inquiry. Please try again.</p> : null}
      <input name="name" required placeholder="Name" className="rounded-md border border-slate-300 px-3 py-2" />
      <input name="email" type="email" placeholder="Email" className="rounded-md border border-slate-300 px-3 py-2" />
      <input name="phone" placeholder="Phone" className="rounded-md border border-slate-300 px-3 py-2" />
      <input name="subject" placeholder="Subject" className="rounded-md border border-slate-300 px-3 py-2" />
      <textarea name="message" required placeholder="Message" className="min-h-32 rounded-md border border-slate-300 px-3 py-2" />
      <button disabled={status === "loading"} className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">
        {status === "loading" ? "Sending..." : "Send inquiry"}
      </button>
    </form>
  );
}

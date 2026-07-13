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
    <form onSubmit={onSubmit} className="aq-card aq-form-grid p-5">
      {status === "success" ? <p className="rounded-md bg-emerald-50 p-3 text-sm font-bold text-emerald-800 md:col-span-2">Inquiry sent successfully.</p> : null}
      {status === "error" ? <p className="rounded-md bg-red-50 p-3 text-sm font-bold text-red-700 md:col-span-2">Could not send inquiry. Please try again.</p> : null}
      <input name="name" required placeholder="Name" className="px-3 py-3" />
      <input name="email" type="email" placeholder="Email" className="px-3 py-3" />
      <input name="phone" placeholder="Phone" className="px-3 py-3" />
      <input name="subject" placeholder="Subject" className="px-3 py-3" />
      <textarea name="message" required placeholder="Message" className="min-h-36 px-3 py-3 md:col-span-2" />
      <button disabled={status === "loading"} className="aq-btn disabled:opacity-60 md:w-fit">
        {status === "loading" ? "Sending..." : "Send inquiry"}
      </button>
    </form>
  );
}

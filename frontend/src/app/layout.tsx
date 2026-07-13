import type { Metadata } from "next";
import { AuthProvider } from "@/auth/auth-provider";
import { getDirection } from "@/i18n";
import { getLocale } from "@/i18n/server";
import "./globals.css";

export const metadata: Metadata = {
  title: "Abnaa Abu Qasaa Trading | Abu Qasaa Platform",
  description: "Premium umbrella business platform for oils, dates, real estate, and import/export services.",
};

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const locale = await getLocale();

  return (
    <html lang={locale} dir={getDirection(locale)}>
      <body>
        <AuthProvider>{children}</AuthProvider>
      </body>
    </html>
  );
}

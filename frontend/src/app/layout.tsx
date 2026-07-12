import type { Metadata } from "next";
import { AuthProvider } from "@/auth/auth-provider";
import "./globals.css";

export const metadata: Metadata = {
  title: "Abnaa Abu Qasaa Trading",
  description: "Umbrella business platform foundation.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body>
        <AuthProvider>{children}</AuthProvider>
      </body>
    </html>
  );
}

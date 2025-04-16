import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "@/stylesheets/globals.css";
import { TailwindIndicator } from "@/components/tailwind-indicator";
import { Toaster } from "sonner";
import DashboardLayout from "@/layouts/dashboard-layout";
import { ThemeProvider } from "@/providers/theme-provider";
import { NextAuthProvider } from "@/providers/next-auth-provider";
import { TanstackProvider } from "@/providers/tanstack-provider";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "Irányítópult",
  description: "CRUD Based Next js App with Laravel",
  icons: {
    icon: [
      {
        url: "/images/favicon.ico",
        href: "/images/favicon.ico",
      },
    ],
    apple: [
      {
        url: "/images/apple-touch-icon.png",
        href: "/images/apple-touch-icon.png",
      },
    ],
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased`}
      >
        <TanstackProvider>
          <ThemeProvider
            enableSystem
            attribute="class"
            defaultTheme="system"
            disableTransitionOnChange
          >
            <NextAuthProvider>
              <DashboardLayout>{children}</DashboardLayout>
              <Toaster position="top-center" />
              <TailwindIndicator />
            </NextAuthProvider>
          </ThemeProvider>
        </TanstackProvider>
      </body>
    </html>
  );
}

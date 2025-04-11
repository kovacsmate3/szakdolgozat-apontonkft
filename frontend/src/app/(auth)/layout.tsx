import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "@/stylesheets/globals.css";
import { ThemeProvider } from "@/components/theme-provider";
import { TailwindIndicator } from "@/components/tailwind-indicator";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "A-Ponton Kft.",
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
        <ThemeProvider
          enableSystem
          attribute="class"
          defaultTheme="system"
          disableTransitionOnChange
        >
          {children}
          <TailwindIndicator />
        </ThemeProvider>
      </body>
    </html>
  );
}

import { auth } from "@/auth";
import StationsPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Töltőállomások",
};

export default async function StationsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <StationsPageClient token={session.user.access_token} />;
}

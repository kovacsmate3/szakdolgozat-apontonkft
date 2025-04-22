import { auth } from "@/auth";
import SitesPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Székhely/Telephelyek",
};

export default async function SitesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <SitesPageClient token={session.user.access_token} />;
}

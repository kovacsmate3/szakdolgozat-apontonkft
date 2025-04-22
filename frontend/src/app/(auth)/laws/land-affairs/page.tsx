import { auth } from "@/auth";
import LandAffairLawsPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Földügy",
};

export default async function LandAffairsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <LandAffairLawsPageClient token={session.user.access_token} />;
}

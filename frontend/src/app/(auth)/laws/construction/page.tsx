import { auth } from "@/auth";
import ConstructionLawsPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Építésügy",
};

export default async function LandMeasurementLawsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <ConstructionLawsPageClient token={session.user.access_token} />;
}

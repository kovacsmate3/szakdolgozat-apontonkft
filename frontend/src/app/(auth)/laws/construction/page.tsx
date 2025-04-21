import { auth } from "@/auth";
import ConstructionLawsPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function LandMeasurementLawsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <ConstructionLawsPageClient token={session.user.access_token} />;
}

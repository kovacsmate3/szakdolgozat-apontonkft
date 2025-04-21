import { auth } from "@/auth";
import LandMeasurementLawsPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function LandMeasurementLawsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <LandMeasurementLawsPageClient token={session.user.access_token} />;
}

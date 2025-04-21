import { auth } from "@/auth";
import LandAffairLawsPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function LandAffairsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <LandAffairLawsPageClient token={session.user.access_token} />;
}

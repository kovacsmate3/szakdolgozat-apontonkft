import { auth } from "@/auth";
import OtherLawsPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function FeesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <OtherLawsPageClient token={session.user.access_token} />;
}

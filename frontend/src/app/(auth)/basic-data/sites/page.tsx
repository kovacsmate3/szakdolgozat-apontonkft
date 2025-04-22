import { auth } from "@/auth";
import SitesPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function SitesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <SitesPageClient token={session.user.access_token} />;
}

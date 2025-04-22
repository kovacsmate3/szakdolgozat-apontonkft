import { auth } from "@/auth";
import StationsPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function StationsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <StationsPageClient token={session.user.access_token} />;
}

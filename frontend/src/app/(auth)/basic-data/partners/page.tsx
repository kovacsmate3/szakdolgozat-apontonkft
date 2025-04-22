import { auth } from "@/auth";
import PartnersPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function PartnersPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <PartnersPageClient token={session.user.access_token} />;
}

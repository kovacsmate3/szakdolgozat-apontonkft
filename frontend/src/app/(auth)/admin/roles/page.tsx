import { auth } from "@/auth";
import RolesPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function RolesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <RolesPageClient token={session.user.access_token} />;
}

import { auth } from "@/auth";
import PermissionsPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function PermissionsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <PermissionsPageClient token={session.user.access_token} />;
}

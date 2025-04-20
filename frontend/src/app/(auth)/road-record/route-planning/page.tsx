import { auth } from "@/auth";
import RoutePlanningPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function RoutePlanningPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  return <RoutePlanningPageClient token={session.user.access_token} />;
}

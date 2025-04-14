import { auth } from "@/auth";
import UsersPageClient from "./page.client";

export const dynamic = "force-dynamic";

export default async function UsersPage() {
  const session = await auth();

  if (!session?.user?.role || session.user.role !== "admin") {
    return <div>Hozzáférés megtagadva.</div>;
  }

  return <UsersPageClient token={session.user.access_token} />;
}

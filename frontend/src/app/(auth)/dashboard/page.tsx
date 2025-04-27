import { auth } from "@/auth";
import { Metadata } from "next";
import DashboardHomePageClient from "./page.client";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Irányítópult",
  description: "Kövesse nyomon járművei használatát és költségeit",
};

export default async function DashboardHomePage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  return (
    <DashboardHomePageClient
      token={session.user.access_token}
      userName={session.user.name || undefined}
      userEmail={session.user.email || undefined}
    />
  );
}

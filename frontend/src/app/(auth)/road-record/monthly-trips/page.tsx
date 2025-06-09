import { auth } from "@/auth";
import MonthlyTripsPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Havi utak",
};

export default async function MonthlyTripsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  const isAdmin = session.user.role === "admin";
  const userId = parseInt(session.user.id);

  return (
    <MonthlyTripsPageClient
      token={session.user.access_token}
      userId={userId}
      isAdmin={isAdmin}
    />
  );
}

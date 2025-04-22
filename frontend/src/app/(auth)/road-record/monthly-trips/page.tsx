// frontend/src/app/(auth)/road-record/monthly-trips/page.tsx
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

  return <MonthlyTripsPageClient token={session.user.access_token} />;
}

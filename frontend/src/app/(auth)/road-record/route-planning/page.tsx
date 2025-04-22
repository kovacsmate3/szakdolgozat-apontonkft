import { auth } from "@/auth";
import RoutePlanningPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Útvonaltervezés",
};

export default async function RoutePlanningPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  return <RoutePlanningPageClient token={session.user.access_token} />;
}

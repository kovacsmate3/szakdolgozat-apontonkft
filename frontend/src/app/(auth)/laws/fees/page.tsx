import { auth } from "@/auth";
import FeesPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Eljárási díjak",
};

export default async function FeesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <FeesPageClient token={session.user.access_token} />;
}

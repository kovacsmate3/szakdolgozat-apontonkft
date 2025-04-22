import { auth } from "@/auth";
import OtherLawsPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "További jogszabályok",
};

export default async function FeesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <OtherLawsPageClient token={session.user.access_token} />;
}

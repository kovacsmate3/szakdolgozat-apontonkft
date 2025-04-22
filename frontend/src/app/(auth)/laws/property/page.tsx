import { auth } from "@/auth";
import PropertyRegistryLawsPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Ingatlan-nyilvántartás",
};

export default async function PropertyRegistryLawsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <PropertyRegistryLawsPageClient token={session.user.access_token} />;
}

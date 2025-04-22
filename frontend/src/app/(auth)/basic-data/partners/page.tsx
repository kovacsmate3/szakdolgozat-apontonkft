import { auth } from "@/auth";
import PartnersPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Partnerek",
};

export default async function PartnersPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <PartnersPageClient token={session.user.access_token} />;
}

import { auth } from "@/auth";
import UsersPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Felhasználók",
};

export default async function UsersPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  return <UsersPageClient token={session.user.access_token} />;
}

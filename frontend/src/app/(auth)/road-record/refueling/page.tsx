import { auth } from "@/auth";
import RefuelingsPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Tankolások/Töltések",
};

export default async function RefuelingsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  // Felhasználói adatok ellenőrzése
  const userId = session.user.id ? parseInt(session.user.id) : 0;
  const isAdmin = session.user.role === "admin";

  return (
    <RefuelingsPageClient
      token={session.user.access_token}
      userId={userId}
      isAdmin={isAdmin}
    />
  );
}

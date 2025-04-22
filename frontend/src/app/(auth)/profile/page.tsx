import { auth } from "@/auth";
import ProfilePageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Profilom",
};

export default async function ProfilePage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  const isAdmin = session.user.role === "admin";

  return (
    <ProfilePageClient
      token={session.user.access_token}
      userId={session.user.id}
      isAdmin={isAdmin}
    />
  );
}

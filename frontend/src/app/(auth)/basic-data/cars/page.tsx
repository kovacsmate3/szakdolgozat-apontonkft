import { auth } from "@/auth";
import CarsPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Autók",
};

export default async function CarsPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  const isAdmin = session.user.role === "admin";
  const userId = parseInt(session.user.id);

  return (
    <CarsPageClient
      token={session.user.access_token}
      isAdmin={isAdmin}
      userId={userId}
    />
  );
}

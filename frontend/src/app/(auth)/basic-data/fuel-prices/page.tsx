import { auth } from "@/auth";
import FuelPricesPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "NAV üzemanyagárak",
};

export default async function FuelPricesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  const isAdmin = session.user.role === "admin";

  return (
    <FuelPricesPageClient token={session.user.access_token} isAdmin={isAdmin} />
  );
}

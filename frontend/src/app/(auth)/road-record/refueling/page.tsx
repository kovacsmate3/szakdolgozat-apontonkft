import { auth } from "@/auth";
import MonthlyFuelExpensesPageClient from "./page.client";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Tankolások/Töltések",
};

export default async function MonthlyFuelExpensesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Bejelentkezés szükséges.</div>;
  }

  return <MonthlyFuelExpensesPageClient token={session.user.access_token} />;
}

import { auth } from "@/auth";
import FuelPricesPageClient from "./page.client";

export const dynamic = "force-dynamic";

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

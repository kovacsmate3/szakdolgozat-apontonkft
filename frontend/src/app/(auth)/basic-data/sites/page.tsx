import { auth } from "@/auth";
import LocationsPageClient from "@/components/(auth)/basic-data/LocationsPageClient";
import { Metadata } from "next";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "Székhely/Telephelyek",
};

export default async function SitesPage() {
  const session = await auth();

  if (!session?.user?.access_token) {
    return <div>Betöltés folyamatban...</div>;
  }

  const isAdmin = session.user.role === "admin";
  const userId = parseInt(session.user.id);

  return (
    <LocationsPageClient
      token={session.user.access_token}
      isAdmin={isAdmin}
      userId={userId}
      locationType="telephely"
      title="Székhely/Telephelyek"
    />
  );
}

import { auth } from "@/auth";
import LocationsPageClient from "@/components/(auth)/basic-data/LocationsPageClient";
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

  const isAdmin = session.user.role === "admin";
  const userId = parseInt(session.user.id);

  // Szűrési lehetőségek a partnerekhez
  const selectOptions = [
    { value: "partner,egyéb,bolt", label: "Összes együttműködés" },
    { value: "partner", label: "Partner" },
    { value: "egyéb", label: "Egyéb" },
    { value: "bolt", label: "Bolt" },
  ];

  return (
    <LocationsPageClient
      token={session.user.access_token}
      isAdmin={isAdmin}
      userId={userId}
      locationType="partner,egyéb,bolt"
      title="Partnerek"
      selectOptions={selectOptions}
    />
  );
}

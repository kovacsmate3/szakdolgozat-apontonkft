"use client";

import { useQuery } from "@tanstack/react-query";
import { Skeleton } from "@/components/ui/skeleton";
import { getLocations } from "@/server/locations";
import { LocationCard } from "@/components/(auth)/basic-data/LocationCard";

interface StationsPageClientProps {
  token: string;
}

export default function StationsPageClient({ token }: StationsPageClientProps) {
  const {
    data: locations,
    isLoading,
    error,
  } = useQuery({
    queryKey: ["locations", "töltőállomás"],
    queryFn: () => getLocations(token, "töltőállomás"),
  });

  if (isLoading) {
    return (
      <div className="container mx-auto py-10">
        <h1 className="text-2xl font-semibold mb-6">Töltőállomások</h1>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {[...Array(6)].map((_, index) => (
            <Skeleton key={index} className="h-40 w-full" />
          ))}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto py-10">
        <h1 className="text-2xl font-semibold mb-6">Hiba történt</h1>
        <p>Nem sikerült betölteni a töltőállomásokat.</p>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-10">
      <h1 className="text-2xl font-semibold mb-6">Töltőállomások</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {locations?.map((location) => (
          <LocationCard key={location.id} location={location} />
        ))}
      </div>
    </div>
  );
}

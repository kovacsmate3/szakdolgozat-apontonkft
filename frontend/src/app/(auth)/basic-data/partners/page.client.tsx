"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { Skeleton } from "@/components/ui/skeleton";
import { getLocations } from "@/server/locations";
import { LocationCard } from "@/components/(auth)/basic-data/LocationCard";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

interface PartnersPageClientProps {
  token: string;
}

export default function PartnersPageClient({ token }: PartnersPageClientProps) {
  const ALL_TYPES = "partner,egyéb,bolt";
  const [locationType, setLocationType] = useState<string | undefined>(
    undefined
  );

  const {
    data: locations,
    isLoading,
    error,
  } = useQuery({
    queryKey: ["locations", locationType],
    queryFn: () => getLocations(token, locationType ?? ALL_TYPES),
    enabled: true,
  });

  if (isLoading) {
    return (
      <div className="container mx-auto py-10">
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-2xl font-semibold">Partnerek</h1>
          <Select disabled>
            <SelectTrigger className="w-[200px]">
              <SelectValue placeholder="Típus szűrése" />
            </SelectTrigger>
          </Select>
        </div>
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
        <p>Nem sikerült betölteni a helyszíneket.</p>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Partnerek</h1>
        <Select
          value={locationType || ""}
          onValueChange={(value) => setLocationType(value || undefined)}
        >
          <SelectTrigger className="w-[200px]">
            <SelectValue placeholder="Típus szűrése" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="partner,egyéb,bolt">
              Összes együttműködés
            </SelectItem>
            <SelectItem value="partner">Partner</SelectItem>
            <SelectItem value="egyéb">Egyéb</SelectItem>
            <SelectItem value="bolt">Bolt</SelectItem>
          </SelectContent>
        </Select>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {locations?.map((location) => (
          <LocationCard key={location.id} location={location} />
        ))}
      </div>
    </div>
  );
}

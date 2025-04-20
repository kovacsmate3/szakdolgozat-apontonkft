"use client";

import RoutePlannerMap from "@/components/(auth)/road-record/route-planning/RoutePlannerMap";
import { MapProvider } from "@/providers/map-provider";
import { getAddresses } from "@/server/addresses";
import { useQuery } from "@tanstack/react-query";

interface Props {
  token: string;
}

export default function RoutePlanningPageClient({ token }: Props) {
  const { data, isLoading } = useQuery({
    queryKey: ["addresses", token],
    queryFn: getAddresses,
  });

  return (
    <>
      <div className="container mx-auto py-10">
        <h1 className="text-2xl font-semibold mb-6">Útvonaltervezés</h1>
        {isLoading ? (
          <p>Címek betöltése...</p>
        ) : (
          <div className="py-4">
            <MapProvider>
              <RoutePlannerMap data={data || []} />
            </MapProvider>
          </div>
        )}
      </div>
    </>
  );
}

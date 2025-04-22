"use client";

import RoutePlannerMap from "@/components/(auth)/road-record/route-planning/RoutePlannerMap";
import { MapProvider } from "@/providers/map-provider";
import { getAddresses } from "@/server/addresses";
import { getCars } from "@/server/cars";
import { getFuelPrices } from "@/server/fuel-prices";
import { useQuery } from "@tanstack/react-query";

interface Props {
  token: string;
}

export default function RoutePlanningPageClient({ token }: Props) {
  // Címek lekérdezése
  const { data: addresses, isLoading: isLoadingAddresses } = useQuery({
    queryKey: ["addresses", token],
    queryFn: getAddresses,
  });

  // Autók lekérdezése
  const { data: cars, isLoading: isLoadingCars } = useQuery({
    queryKey: ["cars", token],
    queryFn: getCars,
  });

  // Üzemanyagárak lekérdezése
  const { data: fuelPrices, isLoading: isLoadingFuelPrices } = useQuery({
    queryKey: ["fuel-prices", token],
    queryFn: getFuelPrices,
  });

  const isLoading = isLoadingAddresses || isLoadingCars || isLoadingFuelPrices;

  return (
    <div className="container mx-auto py-10">
      <h1 className="text-2xl font-semibold mb-6">Útvonaltervezés</h1>
      {isLoading ? (
        <p>Adatok betöltése...</p>
      ) : (
        <div className="py-4">
          <MapProvider>
            <RoutePlannerMap
              addresses={addresses || []}
              cars={cars || []}
              fuelPrices={fuelPrices || []}
            />
          </MapProvider>
        </div>
      )}
    </div>
  );
}

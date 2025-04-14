"use client";

import { useCallback, useEffect, useState } from "react";
import { FuelPrice } from "@/lib/types";
import { FuelPriceTable } from "@/components/(auth)/basic-data/fuel-prices/FuelPriceTable";
import { CreateFuelPriceDialog } from "@/components/(auth)/basic-data/fuel-prices/CreateFuelPriceDialog";
import { columns } from "@/components/(auth)/basic-data/fuel-prices/columns";

interface Props {
  token: string;
  isAdmin: boolean;
}

export default function FuelPricesPageClient({ token, isAdmin }: Props) {
  const [fuel_prices, setFuelPrices] = useState<FuelPrice[]>([]);
  const [loading, setLoading] = useState(true);

  console.log(token);

  const fetchFuelPrices = useCallback(async () => {
    try {
      const res = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/fuel-prices`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (!res.ok)
        throw new Error("Nem sikerült lekérni a NAV üzemanyagárakat.");

      const data = await res.json();
      setFuelPrices(data);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  }, [token]);

  useEffect(() => {
    fetchFuelPrices();
  }, [fetchFuelPrices]);

  const handleFuelPriceCreated = () => {
    fetchFuelPrices();
  };

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">NAV üzemanyagárak</h1>
        {/* Csak admin felhasználóknak jelenítjük meg a gombot */}
        {isAdmin && (
          <CreateFuelPriceDialog onFuelPriceCreated={handleFuelPriceCreated} />
        )}
      </div>
      {loading ? (
        <p>Betöltés...</p>
      ) : (
        <FuelPriceTable columns={columns} data={fuel_prices} />
      )}
    </div>
  );
}

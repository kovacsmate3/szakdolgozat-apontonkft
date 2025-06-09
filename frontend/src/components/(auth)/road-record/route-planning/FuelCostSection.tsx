import { formatHUF, formatPeriodToHungarianMonth } from "@/lib/functions";
import { Car, FuelPrice } from "@/lib/types";
import { Banknote } from "lucide-react";

interface Props {
  fuelCost: number | null;
  selectedCar: Car | null;
  latestFuelPrice: FuelPrice | null;
}

export function FuelCostSection({
  fuelCost,
  selectedCar,
  latestFuelPrice,
}: Props) {
  if (!fuelCost) return null;

  return (
    <div className="pt-2 border-t border-border">
      <div className="flex items-center justify-center gap-2 leading-none">
        <Banknote className="w-4 h-4" />
        <strong className="whitespace-nowrap">
          Becsült üzemanyagköltség:
        </strong>{" "}
        {formatHUF(fuelCost)}
      </div>
      {selectedCar && (
        <p className="text-xs text-muted-foreground mt-1">
          {selectedCar.manufacturer} {selectedCar.model} (
          {selectedCar.standard_consumption} l/100km, {selectedCar.fuel_type})
        </p>
      )}
      {latestFuelPrice && (
        <p className="text-xs text-muted-foreground">
          NAV üzemanyagár:{" "}
          {formatPeriodToHungarianMonth(latestFuelPrice.period)}
        </p>
      )}
    </div>
  );
}

import Image from "next/image";
import { formatHUF, formatPeriodToHungarianMonth } from "@/lib/functions";
import { Car, FuelPrice } from "@/lib/types";
import { useTheme } from "next-themes";

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
  const { theme } = useTheme();

  const iconSrc =
    theme === "dark"
      ? "/images/(auth)/road-record/route-planning/hungarian-forint-price-tag-icon-dark.svg"
      : "/images/(auth)/road-record/route-planning/hungarian-forint-price-tag-icon-light.svg";

  if (!fuelCost) return null;

  return (
    <div className="pt-2 border-t border-border">
      <div className="flex items-center justify-center gap-2 leading-none">
        <div className="relative w-4 h-4">
          <Image
            src={iconSrc}
            alt="Üzemanyag költség ikon"
            fill
            className="object-contain"
          />
        </div>
        <strong className="whitespace-nowrap">Becsült üzemanyagköltség:</strong>{" "}
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

import { format } from "date-fns";
import { Address, Car, FuelPrice, Trip } from "./types";
import { hu } from "date-fns/locale";

export function formatHUF(value: unknown): string {
  const amount =
    typeof value === "string"
      ? parseFloat(value)
      : typeof value === "number"
        ? value
        : NaN;

  if (isNaN(amount)) return "– Ft";

  return (
    new Intl.NumberFormat("hu-HU", {
      style: "decimal",
      maximumFractionDigits: 0,
      minimumFractionDigits: 0,
    }).format(amount) + " Ft"
  );
}

export function formatPeriodToHungarianMonth(dateString: string): string {
  const date = new Date(dateString);
  const firstDay = new Date(date.getFullYear(), date.getMonth());
  return firstDay.toLocaleDateString("hu-HU", {
    year: "numeric",
    month: "long",
  });
}

/**
 * Magyar nyelvű hosszú dátumformátum pl.: "2024. január 14."
 */
export function formatDateToHungarianLong(date: string | Date): string {
  const parsed = new Date(date);
  if (isNaN(parsed.getTime())) return "Érvénytelen dátum";
  return format(parsed, "yyyy. MMMM d.", { locale: hu });
}

export function getFullAddress(address: Address): string {
  return `${address.postalcode} ${address.city}, ${address.road_name} ${address.public_space_type} ${address.building_number}`;
}

export const getFullAddressWithCountry = (addr: Address): string => {
  return `${addr.country}, ${addr.postalcode} ${addr.city}, ${addr.road_name} ${addr.public_space_type} ${addr.building_number}`;
};

/**
 * Üzemanyagköltség kiszámítása
 *
 * @param distanceKm - A megtett távolság kilométerben
 * @param car - A kiválasztott autó objektum
 * @param fuelPrice - Az aktuális üzemanyagár objektum
 * @returns A teljes üzemanyagköltség, vagy null ha nincs elég adat
 */
export const calculateFuelCost = (
  distanceKm: number,
  car: Car | null,
  fuelPrice: FuelPrice | null
): number | null => {
  if (!car || !fuelPrice || !distanceKm) {
    return null;
  }

  // Az autó fogyasztása 100 km-en
  const consumption = car.standard_consumption;

  // A megfelelő üzemanyagár kiválasztása
  let pricePerLiter = 0;
  switch (car.fuel_type.toLowerCase()) {
    case "benzin":
      pricePerLiter = fuelPrice.petrol;
      break;
    case "dízel":
      pricePerLiter = fuelPrice.diesel;
      break;
    case "gáz":
      pricePerLiter = fuelPrice.lp_gas;
      break;
    case "keverék":
      pricePerLiter = fuelPrice.mixture;
      break;
    default:
      pricePerLiter = fuelPrice.petrol;
  }

  // Teljes fogyasztás az úton (liter)
  const totalConsumption = (consumption * distanceKm) / 100;

  // Teljes költség (Ft)
  return totalConsumption * pricePerLiter;
};

/**
 * A legfrissebb üzemanyagár kiválasztása
 *
 * @param fuelPrices - Üzemanyagárak listája
 * @returns A legfrissebb üzemanyagár objektum vagy null
 */
export const getLatestFuelPrice = (
  fuelPrices: FuelPrice[]
): FuelPrice | null => {
  if (!fuelPrices.length) return null;

  return fuelPrices.reduce((latest, current) => {
    const latestDate = new Date(latest.period);
    const currentDate = new Date(current.period);
    return currentDate > latestDate ? current : latest;
  });
};

export function formatDurationHU(seconds: number): string {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;

  const parts = [];
  if (hours > 0) parts.push(`${hours} óra`);
  if (minutes > 0) parts.push(`${minutes} perc`);
  if (secs > 0 || parts.length === 0) parts.push(`${secs} másodperc`);

  return parts.join(" ");
}

/**
 * Calculate the total distance of an array of trips
 */
export function calculateTotalDistance(trips: Trip[]): number {
  return trips.reduce((total, trip) => {
    // Use actual_distance if available, otherwise use planned_distance
    const distance =
      trip.actual_distance !== null
        ? trip.actual_distance
        : trip.end_odometer && trip.start_odometer
          ? trip.end_odometer - trip.start_odometer
          : trip.planned_distance || 0;

    return total + distance;
  }, 0);
}

/**
 * Format a distance value with appropriate units
 */
export function formatDistance(distance: number | null | undefined): string {
  if (distance === null || distance === undefined) return "-";
  return `${distance.toLocaleString("hu-HU", {
    minimumFractionDigits: 1,
    maximumFractionDigits: 1,
  })} km`;
}

/**
 * Format a date in Hungarian format
 */
export function formatDate(date: string | Date): string {
  const d = typeof date === "string" ? new Date(date) : date;
  return d.toLocaleDateString("hu-HU", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

export function roundToTwoDecimals(value: number): number {
  return Number(value.toFixed(2));
}

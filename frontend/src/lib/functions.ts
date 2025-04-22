import { Address } from "./types";

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

export function getFullAddress(address: Address): string {
  return `${address.postalcode} ${address.city}, ${address.road_name} ${address.public_space_type} ${address.building_number}`;
}

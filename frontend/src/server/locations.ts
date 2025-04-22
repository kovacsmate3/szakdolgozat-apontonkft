import { Location } from "@/lib/types";

export async function getLocations(
  token: string,
  locationType?: string
): Promise<Location[]> {
  const url = new URL(`${process.env.NEXT_PUBLIC_API_URL}/locations`);

  if (locationType) {
    url.searchParams.append("location_type", locationType);
  }

  const res = await fetch(url.toString(), {
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
    },
  });

  if (!res.ok) {
    throw new Error("Helyszínek lekérése sikertelen");
  }

  return res.json();
}

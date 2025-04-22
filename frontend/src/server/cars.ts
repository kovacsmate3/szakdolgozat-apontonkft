import { Car } from "@/lib/types";

/**
 * Autók lekérdezése a szerverről
 *
 * A TanStack Query queryFn-ként használható ez a függvény.
 * A token paramétert a queryKey-ből kapja meg.
 */
export const getCars = async ({
  queryKey,
}: {
  queryKey: [string, string];
}): Promise<Car[]> => {
  const [, token] = queryKey;

  if (!token) {
    throw new Error("Hiányzó token az autók lekérdezéséhez.");
  }

  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/cars`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  if (!res.ok) {
    throw new Error("Autók betöltése sikertelen.");
  }

  return res.json();
};

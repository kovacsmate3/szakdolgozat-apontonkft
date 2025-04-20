import { Address } from "@/lib/types";

export const getAddresses = async ({
  queryKey,
}: {
  queryKey: [string, string];
}): Promise<Address[]> => {
  const [, token] = queryKey;

  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/addresses`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  if (!res.ok) {
    throw new Error("Címek betöltése sikertelen.");
  }

  return res.json();
};

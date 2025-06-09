import { TravelPurposeDictionaryApiError } from "@/lib/errors";
import { TravelPurposeDictionary } from "@/lib/types";

export const getTravelPurposes = async ({
  queryKey,
}: {
  queryKey: [string, string];
}): Promise<TravelPurposeDictionary[]> => {
  const [, token] = queryKey;

  if (!token) {
    throw new Error("Hiányzó token az utazási célok lekérdezéséhez.");
  }

  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/travel-purpose-dictionaries`,
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );

  if (!res.ok) {
    throw new Error("Utazási célok betöltése sikertelen.");
  }

  return res.json();
};

export const createTravelPurpose = async ({
  travelPurpose,
  token,
}: {
  travelPurpose: Omit<TravelPurposeDictionary, "id">;
  token: string;
}): Promise<{ travelPurpose: TravelPurposeDictionary; message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/travel-purpose-dictionaries`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
      body: JSON.stringify(travelPurpose),
    }
  );

  const data = await res.json();

  if (!res.ok) {
    throw new TravelPurposeDictionaryApiError(res.status, data);
  }

  return {
    travelPurpose: data.travel_purpose,
    message: data.message || "Az utazási cél sikeresen létrehozva.",
  };
};

export const updateTravelPurpose = async ({
  id,
  travelPurpose,
  token,
}: {
  id: number;
  travelPurpose: Partial<Omit<TravelPurposeDictionary, "id">>;
  token: string;
}): Promise<{ travelPurpose: TravelPurposeDictionary; message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/travel-purpose-dictionaries/${id}`,
    {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
      body: JSON.stringify(travelPurpose),
    }
  );

  const data = await res.json();

  if (!res.ok) {
    throw new TravelPurposeDictionaryApiError(res.status, data);
  }

  return {
    travelPurpose: data.travel_purpose,
    message: data.message || "Az utazási cél adatai sikeresen frissítve.",
  };
};

export const deleteTravelPurpose = async ({
  id,
  token,
}: {
  id: number;
  token: string;
}): Promise<{ message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/travel-purpose-dictionaries/${id}`,
    {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    }
  );

  const data = await res.json();

  if (!res.ok) {
    throw new TravelPurposeDictionaryApiError(res.status, data);
  }

  return {
    message: data.message || "Az utazási cél sikeresen törölve.",
  };
};

import { LocationApiError } from "@/lib/errors";
import { Location } from "@/lib/types";

// Helyszín létrehozáshoz/frissítéshez használt típus
export type LocationInput = Omit<Location, "id" | "address"> & {
  country: string;
  postalcode: string | number;
  city: string;
  road_name: string;
  public_space_type: string;
  building_number: string;
};

// Frissítéshez használt típus, ahol minden mező opcionális
export type LocationUpdateInput = Partial<LocationInput>;

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

// Egy konkrét helyszín lekérdezése ID alapján
export const getLocation = async ({
  locationId,
  token,
}: {
  locationId: number;
  token: string;
}): Promise<Location> => {
  if (!token) {
    throw new Error("Hiányzó token a helyszín lekérdezéséhez.");
  }

  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/locations/${locationId}`,
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );

  if (!res.ok) {
    throw new Error(`Helyszín betöltése sikertelen (ID: ${locationId}).`);
  }

  return res.json();
};

// Új helyszín létrehozása
export const createLocation = async ({
  location,
  token,
}: {
  location: LocationInput;
  token: string;
}): Promise<{ location: Location; message: string }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/locations`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
    body: JSON.stringify(location),
  });

  const data = await res.json();

  if (!res.ok) {
    throw new LocationApiError(res.status, data);
  }

  return {
    location: data.location,
    message: data.message || "A helyszín sikeresen létrehozva.",
  };
};

// Helyszín frissítése
export const updateLocation = async ({
  id,
  location,
  token,
}: {
  id: number;
  location: LocationUpdateInput;
  token: string;
}): Promise<{ location: Location; message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/locations/${id}`,
    {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
      body: JSON.stringify(location),
    }
  );

  const data = await res.json();

  if (!res.ok) {
    throw new LocationApiError(res.status, data);
  }

  return {
    location: data.location,
    message: data.message || "A helyszín adatai sikeresen frissítve lettek.",
  };
};

// Helyszín törlése
export const deleteLocation = async ({
  id,
  token,
}: {
  id: number;
  token: string;
}): Promise<{ message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/locations/${id}`,
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
    throw new LocationApiError(res.status, data);
  }

  return {
    message: data.message || "Helyszín sikeresen törölve.",
  };
};

// frontend/src/server/trips.ts
import { TripApiError } from "@/lib/errors";
import { Trip } from "@/lib/types";

/**
 * Fetch trips data for a specific date range
 */
export const getTrips = async ({
  token,
  startDate,
  endDate,
  carId,
  userId,
}: {
  token: string;
  startDate?: string;
  endDate?: string;
  carId?: number;
  userId?: number;
}): Promise<Trip[]> => {
  const url = new URL(`${process.env.NEXT_PUBLIC_API_URL}/trips`);

  // Add query parameters
  if (startDate) url.searchParams.append("start_date", startDate);
  if (endDate) url.searchParams.append("end_date", endDate);
  if (carId) url.searchParams.append("car_id", carId.toString());
  if (userId) url.searchParams.append("user_id", userId.toString());

  const res = await fetch(url.toString(), {
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
    },
  });

  if (!res.ok) {
    throw new Error("Utak betöltése sikertelen.");
  }

  return res.json();
};

/**
 * Fetch a single trip by ID
 */
export const getTrip = async ({
  token,
  tripId,
}: {
  token: string;
  tripId: number;
}): Promise<Trip> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/trips/${tripId}`,
    {
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
    }
  );

  if (!res.ok) {
    throw new Error(`Az út (ID: ${tripId}) betöltése sikertelen.`);
  }

  return res.json();
};

/**
 * Create a new trip
 */
export const createTrip = async ({
  trip,
  token,
}: {
  trip: Omit<
    Trip,
    | "id"
    | "car"
    | "user"
    | "start_location"
    | "destination_location"
    | "travel_purpose"
  >;
  token: string;
}): Promise<{ trip: Trip; message: string }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/trips`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
    body: JSON.stringify(trip),
  });

  const responseData = await res.json();

  if (!res.ok) {
    throw new TripApiError(res.status, responseData);
  }

  return responseData;
};

/**
 * Update an existing trip
 */
export const updateTrip = async ({
  id,
  trip,
  token,
}: {
  id: number;
  trip: Partial<
    Omit<
      Trip,
      | "id"
      | "car"
      | "user"
      | "start_location"
      | "destination_location"
      | "travel_purpose"
    >
  >;
  token: string;
}): Promise<{ trip: Trip; message: string }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/trips/${id}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
    body: JSON.stringify(trip),
  });

  const responseData = await res.json();

  if (!res.ok) {
    throw new TripApiError(res.status, responseData);
  }

  return responseData;
};

/**
 * Delete a trip
 */
export const deleteTrip = async ({
  tripId,
  token,
}: {
  tripId: number;
  token: string;
}): Promise<{ message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/trips/${tripId}`,
    {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    }
  );

  const responseData = await res.json();

  if (!res.ok) {
    throw new TripApiError(res.status, responseData);
  }

  return responseData;
};

/**
 * Utak exportálása Word (.docx) formátumba
 */
export async function exportTripsToDoc({
  token,
  car_id,
  year,
  month,
}: {
  token: string;
  car_id: number;
  year: number;
  month: number;
}): Promise<Blob> {
  const response = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/trips/export/doc`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        car_id,
        year,
        month,
      }),
    }
  );

  if (!response.ok) {
    const errorData = await response.json();
    throw new Error(errorData.message || "Hiba történt az exportálás során.");
  }

  return response.blob();
}

/**
 * Utak exportálása Excel (.xlsx) formátumba
 */
export async function exportTripsToExcel({
  token,
  car_id,
  year,
  month,
}: {
  token: string;
  car_id: number;
  year: number;
  month?: number; // A hónap opcionális az Excel exportnál (egész éves kimutatás is lehetséges)
}): Promise<Blob> {
  const response = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/trips/export/excel`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        car_id,
        year,
        ...(month ? { month } : {}), // Csak akkor adjuk hozzá, ha meg van adva
      }),
    }
  );

  if (!response.ok) {
    const errorData = await response.json();
    throw new Error(errorData.message || "Hiba történt az exportálás során.");
  }

  return response.blob();
}

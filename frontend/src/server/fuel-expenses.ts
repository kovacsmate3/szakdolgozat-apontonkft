import { FuelExpense } from "@/lib/types";

/**
 * Fetch fuel expenses data for a specific date range
 */
export const getFuelExpenses = async ({
  token,
  startDate,
  endDate,
  carId,
  userId,
  tripId,
}: {
  token: string;
  startDate?: string;
  endDate?: string;
  carId?: number;
  userId?: number;
  tripId?: number;
}): Promise<FuelExpense[]> => {
  const url = new URL(`${process.env.NEXT_PUBLIC_API_URL}/fuel-expenses`);

  // Add query parameters
  if (startDate) url.searchParams.append("from_date", startDate);
  if (endDate) url.searchParams.append("to_date", endDate);
  if (carId) url.searchParams.append("car_id", carId.toString());
  if (userId) url.searchParams.append("user_id", userId.toString());
  if (tripId) url.searchParams.append("trip_id", tripId.toString());

  const res = await fetch(url.toString(), {
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
    },
  });

  if (!res.ok) {
    throw new Error("Tankolási adatok betöltése sikertelen.");
  }

  return res.json();
};

/**
 * Fetch a single fuel expense record by ID
 */
export const getFuelExpense = async ({
  token,
  id,
}: {
  token: string;
  id: number;
}): Promise<FuelExpense> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/fuel-expenses/${id}`,
    {
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
    }
  );

  if (!res.ok) {
    throw new Error(`A tankolási adat (ID: ${id}) betöltése sikertelen.`);
  }

  return res.json();
};

/**
 * Create a new fuel expense record
 */
export const createFuelExpense = async ({
  token,
  data,
}: {
  token: string;
  data: Omit<FuelExpense, "id" | "car" | "user" | "location" | "trip">;
}): Promise<{ message: string; fuel_expense: FuelExpense }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/fuel-expenses`, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });

  if (!res.ok) {
    const errorData = await res.json();
    throw new Error(
      errorData.message || "Hiba történt a tankolási adat létrehozásakor."
    );
  }

  return res.json();
};

/**
 * Update an existing fuel expense record
 */
export const updateFuelExpense = async ({
  token,
  id,
  data,
}: {
  token: string;
  id: number;
  data: Partial<Omit<FuelExpense, "id" | "car" | "user" | "location" | "trip">>;
}): Promise<{ message: string; fuel_expense: FuelExpense }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/fuel-expenses/${id}`,
    {
      method: "PUT",
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    }
  );

  if (!res.ok) {
    const errorData = await res.json();
    throw new Error(
      errorData.message || "Hiba történt a tankolási adat frissítésekor."
    );
  }

  return res.json();
};

/**
 * Delete a fuel expense record
 */
export const deleteFuelExpense = async ({
  token,
  id,
}: {
  token: string;
  id: number;
}): Promise<{ message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/fuel-expenses/${id}`,
    {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );

  if (!res.ok) {
    const errorData = await res.json();
    throw new Error(
      errorData.message || "Hiba történt a tankolási adat törlésekor."
    );
  }

  return res.json();
};

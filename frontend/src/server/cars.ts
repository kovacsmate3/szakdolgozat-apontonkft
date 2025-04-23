import { CarApiError } from "@/lib/errors";
import { Car } from "@/lib/types";

export const getCars = async ({
  queryKey,
}: {
  queryKey: [string, string];
}): Promise<Car[]> => {
  const [, token] = queryKey;

  if (!token) {
    throw new Error("Hiányzó token az autók lekérdezéséhez.");
  }

  console.log("Token:", token);

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

/**
 * Egy konkrét autó lekérdezése ID alapján
 */
export const getCar = async ({
  carId,
  token,
}: {
  carId: number;
  token: string;
}): Promise<Car> => {
  if (!token) {
    throw new Error("Hiányzó token az autó lekérdezéséhez.");
  }

  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/cars/${carId}`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  if (!res.ok) {
    throw new Error(`Autó betöltése sikertelen (ID: ${carId}).`);
  }

  return res.json();
};

/**
 * Új autó létrehozása
 */
export const createCar = async ({
  car,
  token,
}: {
  car: Omit<Car, "id" | "user">;
  token: string;
}): Promise<{ car: Car; message: string }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/cars`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
    body: JSON.stringify(car),
  });

  const data = await res.json();

  if (!res.ok) {
    throw new CarApiError(res.status, data);
  }

  return {
    car: data.car,
    message: data.message || "A jármű sikeresen létrehozva.",
  };
};

/**
 * Autó frissítése
 */
export const updateCar = async ({
  id,
  car,
  token,
}: {
  id: number;
  car: Partial<Omit<Car, "id" | "user">>;
  token: string;
}): Promise<{ car: Car; message: string }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/cars/${id}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
    body: JSON.stringify(car),
  });

  const data = await res.json();

  if (!res.ok) {
    throw new CarApiError(res.status, data);
  }

  return {
    car: data.car,
    message: data.message || "A jármű adatai sikeresen frissítve lettek.",
  };
};

/**
 * Autó törlése
 */
export const deleteCar = async ({
  id,
  token,
}: {
  id: number;
  token: string;
}): Promise<{ message: string }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/cars/${id}`, {
    method: "DELETE",
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
  });

  const data = await res.json();

  if (!res.ok) {
    throw new CarApiError(res.status, data);
  }

  return {
    message: data.message || "Jármű sikeresen törölve.",
  };
};

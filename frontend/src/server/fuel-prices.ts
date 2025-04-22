import { FuelPriceApiError } from "@/lib/errors";
import { FuelPricePayload, FuelPrice } from "@/lib/types";

export const getFuelPrices = async ({
  queryKey,
}: {
  queryKey: [string, string];
}): Promise<FuelPrice[]> => {
  const [, token] = queryKey;

  if (!token) {
    throw new Error("Hiányzó token az üzemanyagárak lekérdezéséhez.");
  }

  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/fuel-prices`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  if (!res.ok) {
    throw new Error("Üzemanyagárak betöltése sikertelen.");
  }

  return res.json();
};

export const createFuelPrice = async ({
  fuelPrice,
  token,
}: {
  fuelPrice: FuelPricePayload;
  token: string;
}): Promise<{ fuelPrice: FuelPrice }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/fuel-prices`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
    body: JSON.stringify(fuelPrice),
  });

  const data = await res.json();
  console.log(data);

  if (!res.ok) {
    throw new FuelPriceApiError(res.status, data);
  }

  const { fuel_price } = data;

  return {
    fuelPrice: {
      ...fuel_price,
    },
  };
};

export const updateFuelPrice = async ({
  id,
  fuelPrice,
  token,
}: {
  id: number;
  fuelPrice: Partial<FuelPricePayload>;
  token: string;
}): Promise<{ fuelPrice: FuelPrice; message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/fuel-prices/${id}`,
    {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
      body: JSON.stringify(fuelPrice),
    }
  );

  const data = await res.json();

  if (!res.ok) {
    throw new FuelPriceApiError(res.status, data);
  }

  return {
    fuelPrice: data.fuel_price,
    message: data.message,
  };
};

export const deleteFuelPrice = async ({
  id,
  token,
}: {
  id: number;
  token: string;
}): Promise<{ message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/fuel-prices/${id}`,
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
    throw new FuelPriceApiError(res.status, data);
  }

  return {
    message: data.message,
  };
};

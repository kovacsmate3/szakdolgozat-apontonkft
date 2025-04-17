import { UserApiError } from "@/lib/errors";
import { CreateUserPayload, UserData } from "@/lib/types";

export const getUsers = async ({
  queryKey,
}: {
  queryKey: [string, string];
}): Promise<UserData[]> => {
  const [, token] = queryKey;

  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/users`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  if (!res.ok) {
    throw new Error("Felhasználók betöltése sikertelen.");
  }

  return res.json();
};

export const createUser = async ({
  user,
  token,
}: {
  user: CreateUserPayload;
  token: string;
}): Promise<{ user: UserData }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/users`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
    body: JSON.stringify(user),
  });

  const data = await res.json();
  console.log(data);

  if (!res.ok) {
    throw new UserApiError(res.status, data);
  }

  return data;
};

export const updateUser = async ({
  id,
  user,
  token,
}: {
  id: number;
  user: Partial<CreateUserPayload>;
  token: string;
}): Promise<{ user: UserData }> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/users/${id}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
    body: JSON.stringify(user),
  });

  const data = await res.json();

  if (!res.ok) {
    throw new UserApiError(res.status, data);
  }

  return data;
};

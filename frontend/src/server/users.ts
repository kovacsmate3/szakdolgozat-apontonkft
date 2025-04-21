import { PasswordChangeApiError, UserApiError } from "@/lib/errors";
import { UserPayload, UserData, PasswordChangeData } from "@/lib/types";

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

export const getUser = async ({
  userId,
  token,
}: {
  userId: string;
  token: string;
}): Promise<UserData> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/users/${userId}`,
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );

  if (!res.ok) {
    throw new Error("Felhasználó betöltése sikertelen.");
  }

  return res.json();
};

export const createUser = async ({
  user,
  token,
}: {
  user: UserPayload;
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

export const changePassword = async ({
  userId,
  data,
  token,
}: {
  userId: number;
  data: PasswordChangeData;
  token: string;
}): Promise<{ message: string }> => {
  const res = await fetch(
    `${process.env.NEXT_PUBLIC_API_URL}/users/${userId}/password`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
      body: JSON.stringify(data),
    }
  );

  const responseData = await res.json();

  if (!res.ok) {
    throw new PasswordChangeApiError(res.status, responseData);
  }

  return responseData;
};

export const updateUser = async ({
  id,
  user,
  token,
}: {
  id: number;
  user: Partial<UserPayload>;
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

export const deleteUser = async ({
  id,
  token,
}: {
  id: number;
  token: string;
}): Promise<void> => {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/users/${id}`, {
    method: "DELETE",
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  if (!res.ok) {
    const data = await res.json();
    throw new UserApiError(res.status, data);
  }
};

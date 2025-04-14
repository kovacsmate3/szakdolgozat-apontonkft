"use client";

import { useCallback, useEffect, useState } from "react";
import { columns } from "@/components/(auth)/admin/users/columns";
import { CreateUserDialog } from "@/components/(auth)/admin/users/CreateUserDialog";
import { UserTable } from "@/components/(auth)/admin/users/UserTable";
import { UserData } from "@/lib/types";

interface Props {
  token: string;
}

export default function UsersPageClient({ token }: Props) {
  const [users, setUsers] = useState<UserData[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchUsers = useCallback(async () => {
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/users`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!res.ok) throw new Error("Nem sikerült lekérni a felhasználókat.");

      const data = await res.json();
      setUsers(data);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  }, [token]);

  useEffect(() => {
    fetchUsers();
  }, [fetchUsers]);

  const handleUserCreated = () => {
    fetchUsers();
  };

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Felhasználók</h1>
        <CreateUserDialog onUserCreated={handleUserCreated} />
      </div>
      {loading ? (
        <p>Betöltés...</p>
      ) : (
        <UserTable columns={columns} data={users} />
      )}
    </div>
  );
}

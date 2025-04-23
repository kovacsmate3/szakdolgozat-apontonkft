"use client";

import { UserForm } from "@/components/(auth)/admin/users/UserForm";
import { DataTable } from "@/components/data-table";
import { columns } from "./columns";
import { useQuery } from "@tanstack/react-query";
import { getUsers } from "@/server/users";

interface Props {
  token: string;
}

export default function UsersPageClient({ token }: Props) {
  const { data, isLoading } = useQuery({
    queryKey: ["users", token],
    queryFn: getUsers,
  });

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Felhasználók</h1>
        <UserForm token={token} />
      </div>
      {isLoading ? (
        <p>Felhasználók betöltése...</p>
      ) : (
        <DataTable columns={columns} data={data || []} filterColumn="email" />
      )}
    </div>
  );
}

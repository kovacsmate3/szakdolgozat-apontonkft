"use client";

import { UserForm } from "@/components/(auth)/admin/users/UserForm";
import { DataTable } from "@/components/data-table";
import { getUsersColumns } from "./columns";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { deleteUser, getUsers } from "@/server/users";
import { useCallback, useMemo, useState } from "react";
import { toast } from "sonner";
import { UserData } from "@/lib/types";
import { DeleteDialog } from "@/components/delete-dialog";
import { Button } from "@/components/ui/button";

interface Props {
  token: string;
}

export default function UsersPageClient({ token }: Props) {
  const queryClient = useQueryClient();
  const [userToEdit, setUserToEdit] = useState<UserData | null>(null);
  const [userToDelete, setUserToDelete] = useState<UserData | null>(null);
  const [formOpen, setFormOpen] = useState(false);

  const { data, isFetching } = useQuery({
    queryKey: ["users", token],
    queryFn: getUsers,
  });

  // Törlés mutáció
  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteUser({ id, token }),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ["users", token] });
      toast.success(`Sikeres törlés!`, {
        duration: 4000,
        description: data.message || "A felhasználó sikeresen törölve.",
      });
      setUserToDelete(null);
    },
    onError: (error) => {
      console.error("Delete error:", error);
      toast.error(`Hiba történt`, {
        description: "A felhasználó törlése sikertelen.",
        duration: 4000,
      });
      setUserToDelete(null);
    },
  });

  // Új felhasználó létrehozásának kezdeményezése
  const onCreateUser = useCallback(() => {
    setUserToEdit(null); // Nincs szerkesztendő felhasználó
    setFormOpen(true); // Megnyitjuk a dialógust
  }, []);

  // Szerkesztés indítása
  const onEdit = useCallback((user: UserData) => {
    setUserToEdit(user); // Beállítjuk a szerkesztendő felhasználót
    setFormOpen(true); // Megnyitjuk a dialógust
  }, []);

  // Törlés indítása
  const onDelete = useCallback((user: UserData) => {
    setUserToDelete(user);
  }, []);

  // Törlés megerősítése
  const handleConfirmDelete = useCallback(() => {
    if (userToDelete?.id) {
      deleteMutation.mutate(userToDelete.id);
    }
  }, [userToDelete, deleteMutation]);

  // Oszlopok előkészítése a műveletekkel
  const columns = useMemo(
    () => getUsersColumns({ onEdit, onDelete }),
    [onEdit, onDelete]
  );

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Felhasználók</h1>
        <Button onClick={onCreateUser}>+ Új felhasználó</Button>
      </div>
      {isFetching ? (
        <p>Felhasználók betöltése...</p>
      ) : (
        <DataTable columns={columns} data={data || []} filterColumn="email" />
      )}

      {/* Törlési dialógus */}
      <DeleteDialog
        isOpen={!!userToDelete}
        onOpenChange={(open) => {
          if (!open) setUserToDelete(null);
        }}
        onConfirm={handleConfirmDelete}
        title="Felhasználó törlése"
        description={
          userToDelete
            ? `Biztosan törölni szeretnéd ${userToDelete.lastname} ${userToDelete.firstname} felhasználót?`
            : "Ez a művelet nem visszavonható."
        }
      />

      {/* Egyetlen UserForm komponens, amit különböző állapotban használunk */}
      <UserForm
        token={token}
        userToEdit={userToEdit}
        isOpen={formOpen}
        onOpenChange={setFormOpen}
      />
    </div>
  );
}

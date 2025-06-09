"use client";

import { useCallback, useMemo, useState } from "react";
import { TravelPurposeDictionary } from "@/lib/types";
import { Button } from "@/components/ui/button";
import { DataTable } from "@/components/data-table";
import { DeleteDialog } from "@/components/delete-dialog";
import { getTravelPurposesColumns } from "./columns";
import { TravelPurposeForm } from "@/components/(auth)/basic-data/travel-purposes/TravelPurposeForm";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  getTravelPurposes,
  deleteTravelPurpose,
} from "@/server/travel-purposes";
import { toast } from "sonner";

interface Props {
  token: string;
  isAdmin: boolean;
  userId: number;
}

export default function TravelPurposesPageClient({
  token,
  isAdmin,
  userId,
}: Props) {
  const queryClient = useQueryClient();
  const [travelPurposeToDelete, setTravelPurposeToDelete] =
    useState<TravelPurposeDictionary | null>(null);
  const [travelPurposeToEdit, setTravelPurposeToEdit] =
    useState<TravelPurposeDictionary | null>(null);
  const [formOpen, setFormOpen] = useState(false);

  const { data: travelPurposes, isFetching } = useQuery({
    queryKey: ["travel-purposes", token],
    queryFn: getTravelPurposes,
  });

  // Törlés mutáció
  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteTravelPurpose({ id, token }),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ["travel-purposes"] });
      toast.success(`Utazási cél sikeresen törölve!`, {
        duration: 4000,
        description: data.message || "Az utazási cél törölve lett.",
      });
      setTravelPurposeToDelete(null);
    },
    onError: (error) => {
      console.error("Delete error:", error);
      toast.error(`Hiba történt`, {
        description: "Az utazási cél törlése sikertelen.",
        duration: 4000,
      });
      setTravelPurposeToDelete(null);
    },
  });

  // Új utazási cél létrehozásának kezdeményezése
  const onCreateTravelPurpose = useCallback(() => {
    setTravelPurposeToEdit(null);
    setFormOpen(true);
  }, []);

  // Szerkesztés indítása
  const onEdit = useCallback(
    (travelPurpose: TravelPurposeDictionary) => {
      // Ellenőrizzük, hogy a felhasználó szerkesztheti-e ezt a rekordot
      if (!isAdmin && travelPurpose.is_system) {
        toast.error("Rendszerszintű utazási célt nem módosíthat.");
        return;
      }

      // Ellenőrizzük, hogy a felhasználó a rekord tulajdonosa-e
      if (!isAdmin && travelPurpose.user_id !== userId) {
        toast.error("Csak a saját utazási céljait módosíthatja.");
        return;
      }

      setTravelPurposeToEdit(travelPurpose);
      setFormOpen(true);
    },
    [isAdmin, userId]
  );

  // Törlés indítása
  const onDelete = useCallback(
    (travelPurpose: TravelPurposeDictionary) => {
      if (isAdmin) {
        // Admin csak a saját maga által létrehozott rendszerszintű rekordot törölheti
        if (travelPurpose.is_system && travelPurpose.user_id !== userId) {
          toast.error(
            "Csak a saját maga által létrehozott rendszerszintű utazási célt törölheti."
          );
          return;
        }
      } else {
        // Nem admin felhasználók esetén
        if (travelPurpose.is_system) {
          toast.error("Rendszerszintű utazási célt nem törölhet.");
          return;
        }

        // Nem admin csak a saját nem rendszerszintű rekordjait törölheti
        if (travelPurpose.user_id !== userId) {
          toast.error("Csak a saját utazási céljait törölheti.");
          return;
        }
      }

      setTravelPurposeToDelete(travelPurpose);
    },
    [isAdmin, userId]
  );

  // Törlés megerősítése
  const handleConfirmDelete = useCallback(() => {
    if (travelPurposeToDelete?.id) {
      deleteMutation.mutate(travelPurposeToDelete.id);
    }
  }, [travelPurposeToDelete, deleteMutation]);

  // Oszlopok előkészítése a műveletekkel
  const columns = useMemo(
    () => getTravelPurposesColumns({ onEdit, onDelete, isAdmin, userId }),
    [onEdit, onDelete, isAdmin, userId]
  );

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Utazás célja szótár</h1>
        <Button onClick={onCreateTravelPurpose}>+ Új utazási cél</Button>
      </div>
      {isFetching ? (
        <p>Utazási célok betöltése...</p>
      ) : (
        <DataTable
          columns={columns}
          data={travelPurposes || []}
          filterColumn="travel_purpose"
        />
      )}

      {/* Törlési dialógus */}
      <DeleteDialog
        isOpen={!!travelPurposeToDelete}
        onOpenChange={(open) => {
          if (!open) setTravelPurposeToDelete(null);
        }}
        onConfirm={handleConfirmDelete}
        title="Utazási cél törlése"
        description={
          travelPurposeToDelete
            ? `Biztosan törölni szeretnéd a(z) "${travelPurposeToDelete.travel_purpose}" utazási célt?`
            : "Ez a művelet nem visszavonható."
        }
      />

      {/* Utazási cél form */}
      <TravelPurposeForm
        token={token}
        travelPurposeToEdit={travelPurposeToEdit}
        isOpen={formOpen}
        onOpenChange={setFormOpen}
        isAdmin={isAdmin}
        userId={userId}
      />
    </div>
  );
}

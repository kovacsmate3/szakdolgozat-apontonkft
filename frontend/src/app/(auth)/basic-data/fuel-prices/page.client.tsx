"use client";

import { FuelPriceForm } from "@/components/(auth)/basic-data/fuel-prices/FuelPriceForm";
import { getFuelPricesColumns } from "./columns";
import { deleteFuelPrice, getFuelPrices } from "@/server/fuel-prices";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { DataTable } from "@/components/data-table";
import { useCallback, useMemo, useState } from "react";
import { FuelPrice } from "@/lib/types";
import { toast } from "sonner";
import { DeleteDialog } from "@/components/delete-dialog";
import { formatPeriodToHungarianMonth } from "@/lib/functions";
import { Button } from "@/components/ui/button";

interface Props {
  token: string;
  isAdmin: boolean;
}

export default function FuelPricesPageClient({ token, isAdmin }: Props) {
  const queryClient = useQueryClient();
  const [fuelPriceToDelete, setFuelPriceToDelete] = useState<FuelPrice | null>(
    null
  );
  const [fuelPriceToEdit, setFuelPriceToEdit] = useState<FuelPrice | null>(
    null
  );
  const [formOpen, setFormOpen] = useState(false);

  const { data, isFetching } = useQuery({
    queryKey: ["fuel-prices", token],
    queryFn: getFuelPrices,
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteFuelPrice({ id, token }),
    onSuccess: (data) => {
      // Invalidate and refetch the fuel prices query
      queryClient.invalidateQueries({ queryKey: ["fuel-prices", token] });
      toast.success(`Sikeres törlés!`, {
        duration: 4000,
        description: data.message || "Az üzemanyagár sikeresen törölve.",
      });
      setFuelPriceToDelete(null);
    },
    onError: (error) => {
      console.error("Delete error:", error);
      toast.error(`Hiba történt`, {
        description: "Az üzemanyagár törlése sikertelen.",
        duration: 4000,
      });
      setFuelPriceToDelete(null);
    },
  });

  const onEdit = useCallback((fuelPrice: FuelPrice) => {
    setFuelPriceToEdit(fuelPrice);
    setFormOpen(true);
  }, []);

  const onDelete = useCallback((fuelPrice: FuelPrice) => {
    setFuelPriceToDelete(fuelPrice);
  }, []);

  const handleConfirmDelete = useCallback(() => {
    if (fuelPriceToDelete?.id) {
      deleteMutation.mutate(fuelPriceToDelete.id);
    }
  }, [fuelPriceToDelete, deleteMutation]);

  const onCreateFuelPrice = useCallback(() => {
    setFuelPriceToEdit(null); // Nincs szerkesztendő üzemanyagár
    setFormOpen(true); // Megnyitjuk a dialógust
  }, []);

  const columns = useMemo(
    () => getFuelPricesColumns({ onEdit, onDelete, isAdmin }),
    [onEdit, onDelete, isAdmin]
  );

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">NAV üzemanyagárak</h1>
        {/* Csak admin felhasználóknak jelenítjük meg a gombot */}
        {isAdmin && (
          <Button onClick={onCreateFuelPrice}>+ Új üzemanyagár</Button>
        )}
      </div>
      {isFetching ? (
        <p>NAV üzemanyagárak betöltése...</p>
      ) : (
        <DataTable columns={columns} data={data || []} filterColumn="period" />
      )}

      {/* Delete confirmation dialog */}
      <DeleteDialog
        isOpen={!!fuelPriceToDelete}
        onOpenChange={(open) => {
          if (!open) setFuelPriceToDelete(null);
        }}
        onConfirm={handleConfirmDelete}
        title="Üzemanyagár törlése"
        description={
          fuelPriceToDelete
            ? `Biztosan törölni szeretnéd a(z) ${formatPeriodToHungarianMonth(fuelPriceToDelete.period)} időszak üzemanyagárait?`
            : "Ez a művelet nem visszavonható."
        }
      />
      {/* Üzemanyagár form komponens */}
      <FuelPriceForm
        token={token}
        initialData={fuelPriceToEdit}
        isOpen={formOpen}
        onOpenChange={setFormOpen}
      />
    </div>
  );
}

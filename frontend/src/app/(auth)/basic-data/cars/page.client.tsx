"use client";

import { useCallback, useMemo, useState } from "react";
import { Car } from "@/lib/types";
import CarCard from "@/components/(auth)/basic-data/cars/CarCard";
import { CarForm } from "@/components/(auth)/basic-data/cars/CarForm";
import { DeleteDialog } from "@/components/delete-dialog";
import { getCars, deleteCar } from "@/server/cars";
import { Button } from "@/components/ui/button";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { toast } from "sonner";
import { CarApiError } from "@/lib/errors";

interface Props {
  token: string;
  isAdmin: boolean;
  userId: number;
}

export default function CarsPageClient({ token, isAdmin, userId }: Props) {
  const queryClient = useQueryClient();
  const [carToDelete, setCarToDelete] = useState<Car | null>(null);
  const [carToEdit, setCarToEdit] = useState<Car | null>(null);
  const [formOpen, setFormOpen] = useState(false);

  const { data: cars, isFetching } = useQuery({
    queryKey: ["cars", token],
    queryFn: getCars,
  });

  // Törlés mutáció
  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteCar({ id, token }),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ["cars"] });
      toast.success(`Jármű sikeresen törölve!`, {
        duration: 4000,
        description: data.message || "A jármű törölve lett.",
      });
      setCarToDelete(null);
    },
    onError: (error: Error) => {
      console.error("Delete error:", error);
      // Ellenőrizzük, hogy CarApiError típusú-e a hiba
      if (error instanceof CarApiError) {
        // Ha igen, akkor használjuk a szerverről érkező hibaüzenetet
        toast.error(`Hiba történt`, {
          description: error.data?.message || "A jármű törlése sikertelen.",
          duration: 4000,
        });
      } else {
        // Egyéb hiba esetén az általános hibaüzenetet jelenítjük meg
        toast.error(`Hiba történt`, {
          description: error.message || "A jármű törlése sikertelen.",
          duration: 4000,
        });
      }
      setCarToDelete(null);
    },
  });

  // Új jármű létrehozásának kezdeményezése - useCallback
  const onCreateCar = useCallback(() => {
    setCarToEdit(null);
    setFormOpen(true);
  }, []);

  // Szerkesztés indítása - useCallback
  const onEdit = useCallback((car: Car) => {
    setCarToEdit(car);
    setFormOpen(true);
  }, []);

  // Törlés indítása - useCallback
  const onDelete = useCallback((car: Car) => {
    setCarToDelete(car);
  }, []);

  // Törlés megerősítése - useCallback
  const handleConfirmDelete = useCallback(() => {
    if (carToDelete?.id) {
      deleteMutation.mutate(carToDelete.id);
    }
  }, [carToDelete, deleteMutation]);

  // Oszlopok előkészítése a műveletekkel - useMemo
  const carList = useMemo(
    () =>
      cars?.map((car) => (
        <CarCard
          key={car.id}
          car={car}
          token={token}
          isAdmin={isAdmin}
          currentUserId={userId}
          onEdit={onEdit}
          onDelete={onDelete}
        />
      )),
    [cars, token, isAdmin, userId, onEdit, onDelete]
  );

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Autók</h1>
        <Button onClick={onCreateCar}>+ Új jármű</Button>
      </div>
      {isFetching ? (
        <p>Autók betöltése...</p>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {carList}
        </div>
      )}

      {/* Törlési dialógus */}
      <DeleteDialog
        isOpen={!!carToDelete}
        onOpenChange={(open) => {
          if (!open) setCarToDelete(null);
        }}
        onConfirm={handleConfirmDelete}
        title="Jármű törlése"
        description={
          carToDelete
            ? `Biztosan törölni szeretnéd a(z) ${carToDelete.manufacturer} ${carToDelete.model} (${carToDelete.license_plate}) járművet?`
            : "Ez a művelet nem visszavonható."
        }
      />

      {/* Jármű form */}
      <CarForm
        token={token}
        carToEdit={carToEdit}
        isOpen={formOpen}
        onOpenChange={setFormOpen}
        currentUserId={userId}
        isAdmin={isAdmin}
      />
    </div>
  );
}

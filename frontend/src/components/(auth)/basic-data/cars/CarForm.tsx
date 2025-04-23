"use client";

import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";

import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
  DialogDescription,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
} from "@/components/ui/form";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { createCar, updateCar } from "@/server/cars";
import { Car, UserData } from "@/lib/types";
import { Loader2 } from "lucide-react";
import { getUsers } from "@/server/users";
import { carFormSchema } from "@/lib/schemas";
import { CarApiError } from "@/lib/errors";

type FormValues = z.infer<typeof carFormSchema>;

interface CarFormProps {
  token: string;
  carToEdit?: Car | null;
  isOpen?: boolean;
  onOpenChange?: (open: boolean) => void;
  currentUserId?: number;
  isAdmin: boolean;
}

export function CarForm({
  token,
  carToEdit = null,
  isOpen: controlledIsOpen,
  onOpenChange: controlledOnOpenChange,
  currentUserId,
  isAdmin,
}: CarFormProps) {
  // Kontrollált vagy nem kontrollált mód
  const isControlled =
    controlledIsOpen !== undefined && controlledOnOpenChange !== undefined;
  const [uncontrolledIsOpen, setUncontrolledIsOpen] = useState(false);

  // Állapot kezelése
  const isOpen = isControlled ? controlledIsOpen : uncontrolledIsOpen;
  const setIsOpen = isControlled
    ? controlledOnOpenChange
    : setUncontrolledIsOpen;

  // Form inicializálása
  const form = useForm<FormValues>({
    resolver: zodResolver(carFormSchema),
    mode: "onChange",
    defaultValues: {
      user_id: currentUserId ? currentUserId.toString() : "",
      car_type: "",
      license_plate: "",
      manufacturer: "",
      model: "",
      fuel_type: "benzin", // alapértelmezett érték
      standard_consumption: "7.0",
      capacity: "1600",
      fuel_tank_capacity: "50",
    },
  });

  const queryClient = useQueryClient();

  // Felhasználók lekérdezése (admin esetén)
  const { data: users } = useQuery({
    queryKey: ["users", token],
    queryFn: getUsers,
    enabled: isAdmin, // Csak admin esetén kérdezzük le
  });

  // Form adatok betöltése szerkesztés esetén
  useEffect(() => {
    if (isOpen && carToEdit) {
      form.reset({
        user_id: carToEdit.user_id.toString(),
        car_type: carToEdit.car_type,
        license_plate: carToEdit.license_plate,
        manufacturer: carToEdit.manufacturer,
        model: carToEdit.model,
        fuel_type: carToEdit.fuel_type,
        standard_consumption: carToEdit.standard_consumption.toString(),
        capacity: carToEdit.capacity.toString(),
        fuel_tank_capacity: carToEdit.fuel_tank_capacity.toString(),
      });
    } else if (isOpen && !carToEdit) {
      // Új autó esetén alapértékek
      form.reset({
        user_id: currentUserId ? currentUserId.toString() : "",
        car_type: "",
        license_plate: "",
        manufacturer: "",
        model: "",
        fuel_type: "benzin",
        standard_consumption: "7.0",
        capacity: "1600",
        fuel_tank_capacity: "50",
      });
    }
  }, [isOpen, carToEdit, form, currentUserId]);

  // Létrehozás mutáció
  const createCarMutation = useMutation({
    mutationKey: ["create-car", token],
    mutationFn: createCar,
    onSuccess: (data: { car: Car }) => {
      queryClient.invalidateQueries({ queryKey: ["cars"] });
      toast.success("Autó sikeresen létrehozva", {
        duration: 4000,
        description: (
          <div className="space-y-1">
            <p>
              {data.car.manufacturer} {data.car.model}
            </p>
            <p>Rendszám: {data.car.license_plate}</p>
          </div>
        ),
      });
      setIsOpen(false);
    },
    onError: (error: unknown) => {
      console.error("Létrehozás hiba:", error);
      if (error instanceof CarApiError && error.data?.errors) {
        Object.entries(error.data.errors).forEach(([field, messages]) => {
          form.setError(field as keyof FormValues, {
            type: "server",
            message: (messages as string[])[0],
          });
        });
      } else {
        toast.error("Hiba történt az autó létrehozása során.");
      }
    },
  });

  // Frissítés mutáció
  const updateCarMutation = useMutation({
    mutationFn: updateCar,
    onSuccess: (data: { car: Car }) => {
      queryClient.invalidateQueries({ queryKey: ["cars"] });
      toast.success("Autó sikeresen frissítve", {
        duration: 4000,
        description: `${data.car.manufacturer} ${data.car.model} (${data.car.license_plate}) adatai frissítve.`,
      });
      setIsOpen(false);
    },
    onError: (error: unknown) => {
      console.error("Szerkesztés hiba:", error);
      if (error instanceof CarApiError && error.data?.errors) {
        Object.entries(error.data.errors).forEach(([field, messages]) => {
          form.setError(field as keyof FormValues, {
            type: "server",
            message: (messages as string[])[0],
          });
        });
      } else {
        toast.error("Hiba történt az autó módosítása során.");
      }
    },
  });

  // Form beküldése
  const onSubmit = (values: FormValues) => {
    // Konvertáljuk a megfelelő típusokra a form adatokat
    const carData = {
      user_id: parseInt(values.user_id),
      car_type: values.car_type,
      license_plate: values.license_plate,
      manufacturer: values.manufacturer,
      model: values.model,
      fuel_type: values.fuel_type,
      standard_consumption: parseFloat(values.standard_consumption),
      capacity: parseInt(values.capacity),
      fuel_tank_capacity: parseInt(values.fuel_tank_capacity),
    };

    if (carToEdit) {
      // Frissítés
      updateCarMutation.mutate({
        id: carToEdit.id,
        car: carData,
        token,
      });
    } else {
      // Létrehozás
      createCarMutation.mutate({
        car: carData,
        token,
      });
    }
  };

  // Modal állapot változása
  const handleOpenChange = (open: boolean) => {
    setIsOpen(open);
  };

  // UI elemek szövegei
  const dialogTitle = carToEdit ? "Jármű szerkesztése" : "Új jármű hozzáadása";
  const dialogDescription = carToEdit
    ? "Módosítsd a jármű adatait"
    : "Add meg az új jármű adatait";
  const buttonText = carToEdit ? "Mentés" : "Létrehozás";
  const isPending = carToEdit
    ? updateCarMutation.isPending
    : createCarMutation.isPending;

  // Üzemanyag típusok
  const fuelTypes = ["benzin", "dízel", "LPG gáz", "keverék"];

  // Dialog tartalma
  const dialogContent = (
    <DialogContent className="w-full max-w-3xl max-h-[95vh] overflow-y-auto p-12">
      <DialogHeader>
        <DialogTitle>{dialogTitle}</DialogTitle>
        <DialogDescription>{dialogDescription}</DialogDescription>
      </DialogHeader>

      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
          {/* Felhasználó kiválasztása (csak adminoknak) */}
          {isAdmin && (
            <FormField
              control={form.control}
              name="user_id"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Tulajdonos</FormLabel>
                  <Select onValueChange={field.onChange} value={field.value}>
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Válassz felhasználót..." />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectGroup>
                        {users?.map((user: UserData) => (
                          <SelectItem key={user.id} value={user.id.toString()}>
                            {user.lastname} {user.firstname} ({user.username})
                          </SelectItem>
                        ))}
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />
          )}

          {/* Autó adatok - 2 oszlopos grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Gyártó és modell */}
            <FormField
              control={form.control}
              name="manufacturer"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Gyártó</FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="pl. Toyota" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="model"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Modell</FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="pl. Corolla" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            {/* Autó típusa és rendszám */}
            <FormField
              control={form.control}
              name="car_type"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Jármű típusa</FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="pl. Sedan" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="license_plate"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Rendszám</FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      placeholder="pl. ABC-123 vagy AB-CD-123"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            {/* Üzemanyag típus és fogyasztás */}
            <FormField
              control={form.control}
              name="fuel_type"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Üzemanyag típus</FormLabel>
                  <Select onValueChange={field.onChange} value={field.value}>
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Válassz üzemanyag típust..." />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectGroup>
                        {fuelTypes.map((type) => (
                          <SelectItem key={type} value={type}>
                            {type.charAt(0).toUpperCase() + type.slice(1)}
                          </SelectItem>
                        ))}
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="standard_consumption"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Normál fogyasztás (l/100km)</FormLabel>
                  <FormControl>
                    <Input {...field} type="number" min="0" step="0.1" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            {/* Hengerűrtartalom és üzemanyagtartály kapacitás */}
            <FormField
              control={form.control}
              name="capacity"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Hengerűrtartalom (cm³)</FormLabel>
                  <FormControl>
                    <Input {...field} type="number" min="1" step="1" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="fuel_tank_capacity"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Üzemanyagtartály kapacitás (l)</FormLabel>
                  <FormControl>
                    <Input {...field} type="number" min="1" step="1" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <DialogFooter className="gap-2 pt-4">
            {carToEdit && (
              <Button
                type="button"
                variant="outline"
                onClick={() => handleOpenChange(false)}
              >
                Mégsem
              </Button>
            )}
            <Button
              type="submit"
              disabled={isPending || !form.formState.isValid}
            >
              {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              {buttonText}
            </Button>
          </DialogFooter>
        </form>
      </Form>
    </DialogContent>
  );

  // Visszatérési érték - csak a dialógus tartalom, nem kell triggert hozzáadni
  return (
    <Dialog open={isOpen} onOpenChange={handleOpenChange}>
      {dialogContent}
    </Dialog>
  );
}

"use client";

import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { toast } from "sonner";
import { format } from "date-fns";
import { Loader2, Plus } from "lucide-react";

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
  FormDescription,
} from "@/components/ui/form";
import { DateTimePicker } from "@/components/ui/date-time-picker";
import { Car, Location, Trip, FuelExpense, UserData } from "@/lib/types";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { createFuelExpense, updateFuelExpense } from "@/server/fuel-expenses";
import { LocationForm } from "@/components/(auth)/basic-data/LocationForm";
import { fuelExpenseFormSchema } from "@/lib/schemas";
import { FuelExpenseApiError } from "@/lib/errors";
import { z } from "zod";

// Define the form values type explicitly based on the schema
type FuelExpenseFormValues = z.infer<typeof fuelExpenseFormSchema>;

// Define an extended form schema that includes the user_id field for admin users
const adminFuelExpenseFormSchema = fuelExpenseFormSchema.extend({
  user_id: z.string().min(1, "Felhasználó kiválasztása kötelező"),
});

// Define a type for the admin form values
type AdminFuelExpenseFormValues = z.infer<typeof adminFuelExpenseFormSchema>;

interface FuelExpenseFormProps {
  token: string;
  expenseToEdit?: FuelExpense | null;
  isOpen?: boolean;
  onOpenChange?: (open: boolean) => void;
  cars: Car[];
  locations: Location[];
  trips: Trip[];
  onLocationCreate?: () => void;
  userId?: number;
  isAdmin?: boolean;
  users?: UserData[];
}

export function FuelExpenseForm({
  token,
  expenseToEdit = null,
  isOpen: controlledIsOpen,
  onOpenChange: controlledOnOpenChange,
  cars,
  locations,
  trips,
  onLocationCreate,
  userId = 0,
  isAdmin = false,
  users = [],
}: FuelExpenseFormProps) {
  const isControlled =
    controlledIsOpen !== undefined && controlledOnOpenChange !== undefined;
  const [uncontrolledIsOpen, setUncontrolledIsOpen] = useState(false);

  const isOpen = isControlled ? controlledIsOpen : uncontrolledIsOpen;
  const setIsOpen = isControlled
    ? controlledOnOpenChange
    : setUncontrolledIsOpen;

  // Új töltőállomás form kezelése
  const [locationFormOpen, setLocationFormOpen] = useState(false);
  const [selectedCarId, setSelectedCarId] = useState<string>("");

  // Select the appropriate form schema based on user role
  const formSchema = isAdmin
    ? adminFuelExpenseFormSchema
    : fuelExpenseFormSchema;

  // Type-safe form with correct schema
  const form = useForm({
    resolver: zodResolver(formSchema),
    defaultValues: {
      car_id: "",
      location_id: "",
      expense_date: new Date(),
      amount: 0,
      currency: "HUF",
      fuel_quantity: 0,
      odometer: 0,
      trip_id: "",
      ...(isAdmin ? { user_id: userId ? userId.toString() : "" } : {}),
    } as typeof isAdmin extends true
      ? AdminFuelExpenseFormValues
      : FuelExpenseFormValues,
  });

  const queryClient = useQueryClient();

  // Az aktuális autóhoz tartozó utak megszűrése
  const filteredTrips = trips.filter(
    (trip) => trip.car_id.toString() === selectedCarId
  );

  // A szűrt helyszínek listája (csak töltőállomások)
  const fuelStations = locations.filter(
    (location) => location.location_type === "töltőállomás"
  );

  // Car ID változásának figyelése a függő értékek frissítéséhez
  useEffect(() => {
    const subscription = form.watch((value, { name }) => {
      if (name === "car_id") {
        setSelectedCarId(value.car_id as string);
        // Ha változik az autó, töröljük a kiválasztott utat
        form.setValue("trip_id", "");
      }
    });

    return () => subscription.unsubscribe();
  }, [form]);

  // Form adatok betöltése szerkesztés esetén
  useEffect(() => {
    if (isOpen && expenseToEdit) {
      const formDefaults = {
        car_id: expenseToEdit.car_id.toString(),
        location_id: expenseToEdit.location_id.toString(),
        expense_date: new Date(expenseToEdit.expense_date),
        amount: expenseToEdit.amount,
        currency: expenseToEdit.currency,
        fuel_quantity: expenseToEdit.fuel_quantity,
        odometer: expenseToEdit.odometer,
        trip_id: expenseToEdit.trip_id ? expenseToEdit.trip_id.toString() : "",
        ...(isAdmin ? { user_id: expenseToEdit.user_id.toString() } : {}),
      } as typeof isAdmin extends true
        ? AdminFuelExpenseFormValues
        : FuelExpenseFormValues;

      form.reset(formDefaults);
      setSelectedCarId(expenseToEdit.car_id.toString());
    } else if (isOpen && !expenseToEdit) {
      // Új tankolás alapértékei
      const formDefaults = {
        car_id: cars.length === 1 ? cars[0].id.toString() : "",
        location_id: "",
        expense_date: new Date(),
        amount: 0,
        currency: "HUF",
        fuel_quantity: 0,
        odometer: 0,
        trip_id: "",
        ...(isAdmin ? { user_id: userId ? userId.toString() : "" } : {}),
      } as typeof isAdmin extends true
        ? AdminFuelExpenseFormValues
        : FuelExpenseFormValues;

      form.reset(formDefaults);
      setSelectedCarId(cars.length === 1 ? cars[0].id.toString() : "");
    }
  }, [isOpen, expenseToEdit, form, cars, isAdmin, userId]);

  // Létrehozás mutáció
  const createFuelExpenseMutation = useMutation({
    mutationFn: createFuelExpense,
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ["fuel-expenses"] });
      queryClient.invalidateQueries({ queryKey: ["calendarData"] });
      toast.success("Tankolás sikeresen rögzítve", {
        duration: 4000,
        description: data.message || "A tankolási adatok rögzítve.",
      });
      setIsOpen(false);
      form.reset();
    },
    onError: (error: FuelExpenseApiError | Error) => {
      console.error("Létrehozás hiba:", error);
      if (error instanceof FuelExpenseApiError && error.data?.errors) {
        // Szerveroldali validációs hibák betöltése a form hibáiba
        Object.entries(error.data.errors || {}).forEach(([field, messages]) => {
          if (field in form.getValues()) {
            // Use a type assertion here to avoid TypeScript errors
            form.setError(field as keyof FuelExpenseFormValues, {
              type: "server",
              message: (messages as string[])[0],
            });
          }
        });
        toast.error("Hibás adatok", {
          description: error.data.message || "Kérjük, ellenőrizze az űrlapot.",
        });
      } else {
        toast.error("Hiba történt a tankolás rögzítése során", {
          description: error.message || "Kérjük, próbálja újra később.",
        });
      }
    },
  });

  // Frissítés mutáció
  const updateFuelExpenseMutation = useMutation({
    mutationFn: updateFuelExpense,
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ["fuel-expenses"] });
      queryClient.invalidateQueries({ queryKey: ["calendarData"] });
      toast.success("Tankolás sikeresen frissítve", {
        duration: 4000,
        description: data.message || "A tankolási adatok módosítva.",
      });
      setIsOpen(false);
    },
    onError: (error: FuelExpenseApiError | Error) => {
      console.error("Frissítés hiba:", error);
      if (error instanceof FuelExpenseApiError && error.data?.errors) {
        // Szerveroldali validációs hibák betöltése a form hibáiba
        Object.entries(error.data.errors || {}).forEach(([field, messages]) => {
          if (field in form.getValues()) {
            // Use a type assertion here to avoid TypeScript errors
            form.setError(field as keyof FuelExpenseFormValues, {
              type: "server",
              message: (messages as string[])[0],
            });
          }
        });
        toast.error("Hibás adatok", {
          description: error.data.message || "Kérjük, ellenőrizze az űrlapot.",
        });
      } else {
        toast.error("Hiba történt a tankolás frissítése során", {
          description: error.message || "Kérjük, próbálja újra később.",
        });
      }
    },
  });

  // Type-safe onSubmit function
  const onSubmit = (
    values: AdminFuelExpenseFormValues | FuelExpenseFormValues
  ) => {
    // Create properly typed data for the API
    const fuelExpenseData = {
      car_id: parseInt(values.car_id),
      location_id: parseInt(values.location_id),
      expense_date: values.expense_date.toISOString(),
      amount: Number(values.amount),
      currency: values.currency,
      fuel_quantity: Number(values.fuel_quantity),
      odometer: Number(values.odometer),
      trip_id:
        values.trip_id && values.trip_id !== "none"
          ? parseInt(values.trip_id)
          : null,
      user_id: isAdmin && values.user_id ? parseInt(values.user_id) : userId,
    };

    if (expenseToEdit) {
      // Frissítés
      updateFuelExpenseMutation.mutate({
        token,
        id: expenseToEdit.id,
        data: fuelExpenseData,
      });
    } else {
      // Létrehozás
      createFuelExpenseMutation.mutate({
        token,
        data: fuelExpenseData,
      });
    }
  };

  // Új töltőállomás létrehozásának kezelése
  const handleAddFuelStation = () => {
    setLocationFormOpen(true);
  };

  // UI elemek szövegei
  const dialogTitle = expenseToEdit
    ? "Tankolás szerkesztése"
    : "Új tankolás rögzítése";
  const dialogDescription = expenseToEdit
    ? "Módosítsa a tankolási adatokat az alábbi űrlapon."
    : "Adja meg az új tankolás adatait az alábbi űrlapon.";
  const buttonText = expenseToEdit ? "Mentés" : "Rögzítés";
  const isPending = expenseToEdit
    ? updateFuelExpenseMutation.isPending
    : createFuelExpenseMutation.isPending;

  // Helyszínek rendezése név szerint
  const sortedFuelStations = [...fuelStations].sort((a, b) =>
    a.name.localeCompare(b.name)
  );

  return (
    <>
      <Dialog open={isOpen} onOpenChange={setIsOpen}>
        <DialogContent className="w-full max-w-3xl max-h-[95vh] overflow-y-auto p-8">
          <DialogHeader className="mb-4">
            <DialogTitle>{dialogTitle}</DialogTitle>
            <DialogDescription>{dialogDescription}</DialogDescription>
          </DialogHeader>

          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {/* Jármű kiválasztása */}
                <FormField
                  control={form.control}
                  name="car_id"
                  render={({ field }) => (
                    <FormItem className="flex flex-col">
                      <FormLabel>Autó</FormLabel>
                      <Select
                        onValueChange={field.onChange}
                        value={field.value}
                      >
                        <FormControl>
                          <SelectTrigger className="w-full">
                            <SelectValue
                              placeholder="Válasszon autót"
                              className="truncate"
                            />
                          </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                          <SelectGroup>
                            {cars.map((car) => (
                              <SelectItem
                                key={car.id}
                                value={car.id.toString()}
                              >
                                {car.manufacturer} {car.model} (
                                {car.license_plate})
                              </SelectItem>
                            ))}
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                {/* Töltőállomás kiválasztása */}
                <FormField
                  control={form.control}
                  name="location_id"
                  render={({ field }) => (
                    <FormItem className="flex flex-col">
                      <FormLabel>Töltőállomás</FormLabel>
                      <div className="relative h-10">
                        <div className="absolute inset-0 right-12 h-full">
                          <Select
                            onValueChange={field.onChange}
                            value={field.value}
                          >
                            <FormControl>
                              <SelectTrigger className="h-full w-full">
                                <SelectValue
                                  placeholder="Válasszon töltőállomást"
                                  className="truncate"
                                />
                              </SelectTrigger>
                            </FormControl>
                            <SelectContent>
                              <SelectGroup>
                                {sortedFuelStations.map((location) => (
                                  <SelectItem
                                    key={location.id}
                                    value={location.id.toString()}
                                    className="truncate"
                                  >
                                    {location.name}
                                  </SelectItem>
                                ))}
                              </SelectGroup>
                            </SelectContent>
                          </Select>
                        </div>
                        <div className="absolute right-0 top-0 h-full">
                          <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={handleAddFuelStation}
                          >
                            <Plus className="h-4 w-4" />
                          </Button>
                        </div>
                      </div>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              {/* Admin felhasználó választó */}
              {isAdmin && (
                <div className="grid grid-cols-1 gap-4">
                  {/* Use a type assertion to tell TypeScript this is valid */}
                  <FormField
                    control={form.control}
                    name={"user_id"}
                    render={({ field }) => (
                      <FormItem className="flex flex-col">
                        <FormLabel>Felhasználó</FormLabel>
                        <Select
                          onValueChange={field.onChange}
                          value={field.value}
                        >
                          <FormControl>
                            <SelectTrigger className="w-full">
                              <SelectValue
                                placeholder="Válasszon felhasználót"
                                className="truncate"
                              />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectGroup>
                              {users.map((user) => (
                                <SelectItem
                                  key={user.id}
                                  value={user.id.toString()}
                                >
                                  {user.firstname} {user.lastname} (
                                  {user.username})
                                </SelectItem>
                              ))}
                            </SelectGroup>
                          </SelectContent>
                        </Select>
                        <FormDescription className="text-xs">
                          A tankolási adat tulajdonosa.
                        </FormDescription>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>
              )}

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {/* Tankolás időpontja */}
                <FormField
                  control={form.control}
                  name="expense_date"
                  render={({ field }) => (
                    <FormItem className="flex flex-col h-full">
                      <FormLabel>Tankolás időpontja</FormLabel>
                      <FormControl>
                        <DateTimePicker
                          date={field.value}
                          setDate={field.onChange}
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                {/* Kapcsolódó út (ha van) */}
                <FormField
                  control={form.control}
                  name="trip_id"
                  render={({ field }) => (
                    <FormItem className="flex flex-col h-full">
                      <FormLabel>Kapcsolódó út (opcionális)</FormLabel>
                      <div className="flex-grow">
                        <Select
                          onValueChange={field.onChange}
                          value={field.value ?? ""}
                        >
                          <FormControl>
                            <SelectTrigger className="w-full">
                              <SelectValue
                                placeholder="Válasszon kapcsolódó utat"
                                className="truncate"
                              />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent className="truncate">
                            <SelectGroup>
                              <SelectItem value="none">
                                Nincs kapcsolódó út
                              </SelectItem>
                              {filteredTrips.map((trip) => {
                                const startDate = new Date(trip.start_time);
                                const formattedDate = format(
                                  startDate,
                                  "yyyy.MM.dd HH:mm"
                                );

                                return (
                                  <SelectItem
                                    key={trip.id}
                                    value={trip.id.toString()}
                                    className="truncate"
                                  >
                                    <span className="truncate block">
                                      {formattedDate}:{" "}
                                      {trip.start_location?.name} →{" "}
                                      {trip.destination_location?.name}
                                    </span>
                                  </SelectItem>
                                );
                              })}
                            </SelectGroup>
                          </SelectContent>
                        </Select>
                      </div>
                      <FormDescription className="mt-1 text-xs">
                        Opcionálisan kiválaszthat egy utat, amihez a tankolás
                        tartozik.
                      </FormDescription>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {/* Kilométeróra állás */}
                <FormField
                  control={form.control}
                  name="odometer"
                  render={({ field }) => (
                    <FormItem className="flex flex-col h-full">
                      <div className="sm:h-12 flex items-end sm:pb-2">
                        <FormLabel>Kilométeróra állás (km)</FormLabel>
                      </div>
                      <FormControl>
                        <Input
                          type="number"
                          placeholder="pl. 45230"
                          {...field}
                          value={field.value === 0 ? "" : field.value}
                          onChange={(e) =>
                            field.onChange(Number(e.target.value))
                          }
                          className="h-10"
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                {/* Üzemanyag mennyiség */}
                <FormField
                  control={form.control}
                  name="fuel_quantity"
                  render={({ field }) => (
                    <FormItem className="flex flex-col h-full">
                      <div className="sm:h-12 flex items-end sm:pb-2">
                        <FormLabel>Tankolás mennyisége (liter)</FormLabel>
                      </div>
                      <FormControl>
                        <Input
                          type="number"
                          placeholder="pl. 45.5"
                          step="0.01"
                          {...field}
                          value={field.value === 0 ? "" : field.value}
                          onChange={(e) =>
                            field.onChange(Number(e.target.value))
                          }
                          className="h-10"
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                {/* Fizetett összeg */}
                <FormField
                  control={form.control}
                  name="amount"
                  render={({ field }) => (
                    <FormItem className="flex flex-col h-full">
                      <div className="sm:h-12 flex items-end sm:pb-2">
                        <FormLabel>Fizetett összeg (Ft)</FormLabel>
                      </div>
                      <FormControl>
                        <Input
                          type="number"
                          placeholder="pl. 25450"
                          {...field}
                          value={field.value === 0 ? "" : field.value}
                          onChange={(e) =>
                            field.onChange(Number(e.target.value))
                          }
                          className="h-10"
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <DialogFooter className="gap-2 pt-4">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setIsOpen(false)}
                >
                  Mégsem
                </Button>
                <Button
                  type="submit"
                  disabled={isPending || !form.formState.isValid}
                >
                  {isPending && (
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                  )}
                  {buttonText}
                </Button>
              </DialogFooter>
            </form>
          </Form>
        </DialogContent>
      </Dialog>

      {/* Töltőállomás létrehozás form */}
      {isOpen && locationFormOpen && (
        <LocationForm
          token={token}
          locationToEdit={null}
          isOpen={locationFormOpen}
          onOpenChange={(open) => {
            setLocationFormOpen(open);
            if (!open && onLocationCreate) {
              onLocationCreate();
            }
          }}
          currentUserId={userId}
          isAdmin={isAdmin}
          defaultLocationType="töltőállomás"
          allowedLocationTypes={["töltőállomás"]}
          allowTypeSelection={false}
        />
      )}
    </>
  );
}

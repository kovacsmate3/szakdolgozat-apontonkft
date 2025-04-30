"use client";

import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";
import { Loader2, Plus, AlertCircle } from "lucide-react";

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
import { Switch } from "@/components/ui/switch";
import { Separator } from "@/components/ui/separator";
import { Card, CardContent } from "@/components/ui/card";
import {
  Car,
  Location,
  Trip,
  TravelPurposeDictionary,
  UserData,
} from "@/lib/types";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { createTrip, updateTrip } from "@/server/trips";
import { LocationForm } from "@/components/(auth)/basic-data/LocationForm";
import { tripFormSchema } from "@/lib/schemas";
import { TripApiError } from "@/lib/errors";
import { formatLocalDateTime } from "@/lib/functions";

// Define the form values type explicitly based on tripFormSchema
type TripFormValues = z.infer<typeof tripFormSchema>;

interface TripFormProps {
  token: string;
  tripToEdit?: Trip | null;
  isOpen?: boolean;
  onOpenChange?: (open: boolean) => void;
  cars: Car[];
  locations: Location[];
  users?: UserData[];
  travelPurposes: TravelPurposeDictionary[];
  onLocationCreate?: () => void;
  isAdmin?: boolean;
  userId?: number;
  initialDate?: Date;
}

export function TripForm({
  token,
  tripToEdit = null,
  isOpen: controlledIsOpen,
  onOpenChange: controlledOnOpenChange,
  cars,
  locations,
  travelPurposes,
  onLocationCreate,
  userId = 0,
  isAdmin = false,
  users = [],
  initialDate,
}: TripFormProps) {
  const isControlled =
    controlledIsOpen !== undefined && controlledOnOpenChange !== undefined;
  const [uncontrolledIsOpen, setUncontrolledIsOpen] = useState(false);

  const isOpen = isControlled ? controlledIsOpen : uncontrolledIsOpen;
  const setIsOpen = isControlled
    ? controlledOnOpenChange
    : setUncontrolledIsOpen;

  // Új helyszín form kezelése
  const [locationFormOpen, setLocationFormOpen] = useState(false);
  const [locationType, setLocationType] = useState<"start" | "destination">(
    "start"
  );

  const form = useForm({
    resolver: zodResolver(tripFormSchema),
    defaultValues: {
      car_id: "",
      start_location_id: "",
      destination_location_id: "",
      start_time: new Date(),
      end_time: new Date(),
      start_odometer: undefined,
      end_odometer: undefined,
      actual_distance: undefined,
      use_odometer: true,
      dict_id: "none",
      ...(isAdmin ? { user_id: userId ? userId.toString() : "" } : {}),
    },
  });

  const queryClient = useQueryClient();

  // Form adatok betöltése szerkesztés esetén
  useEffect(() => {
    if (isOpen && tripToEdit) {
      form.reset({
        car_id: tripToEdit.car_id.toString(),
        start_location_id: tripToEdit.start_location_id.toString(),
        destination_location_id: tripToEdit.destination_location_id.toString(),
        start_time: new Date(tripToEdit.start_time),
        end_time: tripToEdit.end_time
          ? new Date(tripToEdit.end_time)
          : undefined,
        start_odometer: tripToEdit.start_odometer || undefined,
        end_odometer: tripToEdit.end_odometer || undefined,
        actual_distance: tripToEdit.actual_distance || undefined,
        use_odometer: !!(tripToEdit.start_odometer && tripToEdit.end_odometer),
        dict_id: tripToEdit.dict_id ? tripToEdit.dict_id.toString() : "none",
        ...(isAdmin
          ? {
              user_id: userId ? userId.toString() : "",
            }
          : {}),
      });
    } else if (isOpen && !tripToEdit) {
      // Új utazás alapértékei
      const defaultDate = initialDate || new Date();
      form.reset({
        car_id: cars.length === 1 ? cars[0].id.toString() : "",
        start_location_id: "",
        destination_location_id: "",
        start_time: defaultDate,
        end_time: defaultDate,
        start_odometer: undefined,
        end_odometer: undefined,
        actual_distance: undefined,
        use_odometer: true,
        dict_id: "none", // Changed from empty string to "none"
        ...(isAdmin
          ? {
              user_id: userId ? userId.toString() : "",
            }
          : {}),
      });
    }
  }, [isOpen, tripToEdit, form, cars, isAdmin, userId, initialDate]);

  // Kilométeróra váltó értékének figyelése
  const useOdometer = form.watch("use_odometer");

  // Kezdő és záró kilométeróra figyelése az automatikus távolság kiszámításához
  const startOdometer = form.watch("start_odometer");
  const endOdometer = form.watch("end_odometer");

  // Automatikus távolság kiszámítás, ha mindkét kilométeróra érték meg van adva
  useEffect(() => {
    if (
      useOdometer &&
      startOdometer !== undefined &&
      endOdometer !== undefined &&
      endOdometer >= startOdometer
    ) {
      form.setValue("actual_distance", endOdometer - startOdometer);
    }
  }, [startOdometer, endOdometer, useOdometer, form]);

  // Létrehozás mutáció
  const createTripMutation = useMutation({
    mutationFn: createTrip,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["trips"] });
      queryClient.invalidateQueries({ queryKey: ["calendarData"] });
      toast.success("Utazás sikeresen létrehozva", {
        duration: 4000,
        description: `Az utazás adatai rögzítve.`,
      });
      setIsOpen(false);
      form.reset();
    },
    onError: (error: TripApiError | Error) => {
      console.error("Létrehozás hiba:", error);
      if ("data" in error && error.data?.errors) {
        // Szerveroldali validációs hibák betöltése a form hibáiba
        Object.entries(error.data.errors || {}).forEach(([field, messages]) => {
          if (field in form.getValues()) {
            form.setError(field as keyof TripFormValues, {
              type: "server",
              message: (messages as string[])[0],
            });
          }
        });
        toast.error("Hibás adatok", {
          description: error.data.message || "Kérjük, ellenőrizze az űrlapot.",
        });
      } else {
        toast.error("Hiba történt az utazás létrehozása során", {
          description: error.message || "Kérjük, próbálja újra később.",
        });
      }
    },
  });

  // Frissítés mutáció
  const updateTripMutation = useMutation({
    mutationFn: updateTrip,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["trips"] });
      queryClient.invalidateQueries({ queryKey: ["calendarData"] });

      queryClient.refetchQueries({ queryKey: ["trips"] });
      queryClient.refetchQueries({ queryKey: ["calendarData"] });
      toast.success("Utazás sikeresen frissítve", {
        duration: 4000,
        description: `Az utazás adatai módosítva.`,
      });
      setIsOpen(false);
    },
    onError: (error: TripApiError | Error) => {
      console.error("Frissítés hiba:", error);
      if ("data" in error && error.data?.errors) {
        // Szerveroldali validációs hibák betöltése a form hibáiba
        Object.entries(error.data.errors || {}).forEach(([field, messages]) => {
          if (field in form.getValues()) {
            form.setError(field as keyof TripFormValues, {
              type: "server",
              message: (messages as string[])[0],
            });
          }
        });
        toast.error("Hibás adatok", {
          description: error.data.message || "Kérjük, ellenőrizze az űrlapot.",
        });
      } else {
        toast.error("Hiba történt az utazás frissítése során", {
          description: error.message || "Kérjük, próbálja újra később.",
        });
      }
    },
  });

  const formatDuration = (start: Date, end: Date): string => {
    const durationMs = end.getTime() - start.getTime();
    const hours = Math.floor(durationMs / (1000 * 60 * 60));
    const minutes = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((durationMs % (1000 * 60)) / 1000);

    return `${hours.toString().padStart(2, "0")}:${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
  };

  const onSubmit = (values: TripFormValues) => {
    // Időpontok ellenőrzése
    if (values.start_time > (values.end_time || new Date())) {
      form.setError("end_time", {
        message: "Az érkezési idő nem lehet korábbi, mint az indulási idő.",
      });
      return;
    }

    // Az időtartam kiszámítása
    const actual_duration = values.end_time
      ? formatDuration(values.start_time, values.end_time)
      : null;

    // Fix the type issues by ensuring all undefined values are converted to null
    const tripData = {
      car_id: parseInt(values.car_id),
      start_location_id: parseInt(values.start_location_id),
      destination_location_id: parseInt(values.destination_location_id),
      start_time: formatLocalDateTime(values.start_time),
      end_time: values.end_time ? formatLocalDateTime(values.end_time) : null,
      start_odometer: useOdometer ? (values.start_odometer ?? null) : null,
      end_odometer: useOdometer ? (values.end_odometer ?? null) : null,
      actual_distance: useOdometer
        ? values.start_odometer !== undefined &&
          values.end_odometer !== undefined &&
          values.end_odometer >= values.start_odometer
          ? values.end_odometer - values.start_odometer
          : (values.actual_distance ?? null)
        : (values.actual_distance ?? null),
      actual_duration,
      planned_distance: null,
      planned_duration: null,
      dict_id:
        values.dict_id && values.dict_id !== "none"
          ? parseInt(values.dict_id)
          : null,
      user_id: isAdmin && values.user_id ? parseInt(values.user_id) : userId,
    };

    if (tripToEdit) {
      // Frissítés
      updateTripMutation.mutate({
        id: tripToEdit.id,
        trip: tripData,
        token,
      });
    } else {
      // Létrehozás
      createTripMutation.mutate({
        trip: tripData,
        token,
      });
    }
  };

  // Helyszín létrehozás kezelése
  const handleAddLocation = (type: "start" | "destination") => {
    setLocationType(type);
    setLocationFormOpen(true);
  };

  // UI elemek szövegei
  const dialogTitle = tripToEdit
    ? "Utazás szerkesztése"
    : "Új utazás létrehozása";
  const dialogDescription = tripToEdit
    ? "Módosítsa az utazás adatait az alábbi űrlapon."
    : "Adja meg az új utazás adatait az alábbi űrlapon.";
  const buttonText = tripToEdit ? "Mentés" : "Létrehozás";
  const isPending = tripToEdit
    ? updateTripMutation.isPending
    : createTripMutation.isPending;

  // Helyszínek rendezése név szerint
  const sortedLocations = [...locations].sort((a, b) =>
    a.name.localeCompare(b.name)
  );

  return (
    <>
      <Dialog open={isOpen} onOpenChange={setIsOpen}>
        <DialogContent className="w-full max-w-3xl max-h-[95vh] overflow-y-auto p-6 sm:p-8 md:p-10">
          <DialogHeader className="mb-4">
            <DialogTitle>{dialogTitle}</DialogTitle>
            <DialogDescription>{dialogDescription}</DialogDescription>
          </DialogHeader>

          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
              {/* Jármű és utazási cél - egy sorban */}
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

                {/* Utazás célja */}
                <FormField
                  control={form.control}
                  name="dict_id"
                  render={({ field }) => (
                    <FormItem className="flex flex-col">
                      <FormLabel>Utazás célja</FormLabel>
                      <Select
                        onValueChange={field.onChange}
                        value={field.value || "none"}
                      >
                        <FormControl>
                          <SelectTrigger className="w-full">
                            <SelectValue
                              placeholder="Válasszon utazási célt"
                              className="truncate"
                            />
                          </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="none">Nincs megadva</SelectItem>
                            {travelPurposes
                              .filter((purpose) => purpose.type === "Üzleti")
                              .map((purpose) => (
                                <SelectItem
                                  key={purpose.id}
                                  value={purpose.id.toString()}
                                >
                                  {purpose.travel_purpose}
                                </SelectItem>
                              ))}
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <FormDescription className="text-xs">
                        Az utazás üzleti célját adja meg.
                      </FormDescription>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <Separator />
              {isAdmin && (
                <div className="grid grid-cols-1 gap-4">
                  <FormField
                    control={form.control}
                    name="user_id"
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
                                  {user.lastname} {user.firstname} (
                                  {user.username})
                                </SelectItem>
                              ))}
                            </SelectGroup>
                          </SelectContent>
                        </Select>
                        <FormDescription className="text-xs">
                          Az utazási adat tulajdonosa.
                        </FormDescription>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>
              )}

              <div className="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-4">
                {/* Indulási helyszín */}
                <div className="space-y-2">
                  <FormField
                    control={form.control}
                    name="start_location_id"
                    render={({ field }) => (
                      <FormItem className="flex flex-col">
                        <FormLabel>Indulási helyszín</FormLabel>
                        <Select
                          onValueChange={field.onChange}
                          value={field.value || ""}
                        >
                          <FormControl>
                            <SelectTrigger className="w-full">
                              <SelectValue
                                className="truncate"
                                placeholder="Válasszon indulási helyszínt"
                              />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectGroup>
                              {sortedLocations.map((location) => (
                                <SelectItem
                                  key={location.id}
                                  value={location.id.toString()}
                                >
                                  {location.name}
                                </SelectItem>
                              ))}
                            </SelectGroup>
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="w-full"
                    onClick={() => handleAddLocation("start")}
                  >
                    <Plus className="h-4 w-4 mr-1" />
                    Új indulási helyszín
                  </Button>
                </div>

                {/* Érkezési helyszín */}
                <div className="space-y-2">
                  <FormField
                    control={form.control}
                    name="destination_location_id"
                    render={({ field }) => (
                      <FormItem className="flex flex-col">
                        <FormLabel>Érkezési helyszín</FormLabel>
                        <Select
                          onValueChange={field.onChange}
                          value={field.value || ""}
                        >
                          <FormControl>
                            <SelectTrigger className="w-full">
                              <SelectValue
                                className="truncate"
                                placeholder="Válasszon érkezési helyszínt"
                              />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectGroup>
                              {sortedLocations.map((location) => (
                                <SelectItem
                                  key={location.id}
                                  value={location.id.toString()}
                                >
                                  {location.name}
                                </SelectItem>
                              ))}
                            </SelectGroup>
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="w-full"
                    onClick={() => handleAddLocation("destination")}
                  >
                    <Plus className="h-4 w-4 mr-1" />
                    Új érkezési helyszín
                  </Button>
                </div>
              </div>

              {/* És ezután külön blokkban: időpontok egymás alatt */}
              <div className="space-y-6 mt-6">
                <FormField
                  control={form.control}
                  name="start_time"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Indulási idő</FormLabel>
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

                <FormField
                  control={form.control}
                  name="end_time"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Érkezési idő</FormLabel>
                      <FormControl>
                        <DateTimePicker
                          date={field.value || new Date()}
                          setDate={field.onChange}
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              {/* Helyszínek rész - átrendezve */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-4">
                {/* Kezdő kilométeróra */}
                {useOdometer && (
                  <FormField
                    control={form.control}
                    name="start_odometer"
                    render={({ field }) => (
                      <FormItem className="mt-4">
                        <FormLabel>Kezdő kilométeróra (km)</FormLabel>
                        <FormControl>
                          <Input
                            type="number"
                            {...field}
                            value={field.value === undefined ? "" : field.value}
                            onChange={(e) => {
                              const value =
                                e.target.value === ""
                                  ? undefined
                                  : parseInt(e.target.value);
                              field.onChange(value);
                              // Ellenőrizzük, hogy hiba történt-e
                              console.log(
                                "Field value changed:",
                                value,
                                "Errors:",
                                form.formState.errors
                              );
                            }}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                )}

                {/* Záró kilométeróra */}
                {useOdometer && (
                  <FormField
                    control={form.control}
                    name="end_odometer"
                    render={({ field }) => (
                      <FormItem className="mt-4">
                        <FormLabel>Záró kilométeróra (km)</FormLabel>
                        <FormControl>
                          <Input
                            type="number"
                            {...field}
                            value={field.value === undefined ? "" : field.value}
                            onChange={(e) =>
                              field.onChange(
                                e.target.value === ""
                                  ? undefined
                                  : parseInt(e.target.value)
                              )
                            }
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                )}
              </div>

              <FormField
                control={form.control}
                name="use_odometer"
                render={({ field }) => (
                  <FormItem className="flex flex-row items-center justify-between rounded-lg border p-4 shadow-sm">
                    <div className="space-y-0.5">
                      <FormLabel>Kilométeróra alapján</FormLabel>
                      <FormDescription>
                        Kilométeróra állások alapján számítsa a távolságot
                      </FormDescription>
                    </div>
                    <FormControl>
                      <Switch
                        checked={field.value}
                        onCheckedChange={field.onChange}
                      />
                    </FormControl>
                  </FormItem>
                )}
              />

              {/* Távolság manuális megadása, ha nem kilométeróra alapján */}
              {!useOdometer && (
                <FormField
                  control={form.control}
                  name="actual_distance"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Megtett távolság (km)</FormLabel>
                      <FormControl>
                        <Input
                          type="number"
                          {...field}
                          value={field.value === undefined ? "" : field.value}
                          onChange={(e) =>
                            field.onChange(
                              e.target.value === ""
                                ? undefined
                                : parseFloat(e.target.value)
                            )
                          }
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              )}

              {/* Calculated distance display */}
              {useOdometer &&
                startOdometer !== undefined &&
                endOdometer !== undefined && (
                  <Card className="bg-background border-border">
                    <CardContent className="p-4 flex items-center space-x-2">
                      <AlertCircle className="h-4 w-4 text-primary" />
                      <p className="text-sm">
                        Megtett távolság:{" "}
                        <strong>{endOdometer - startOdometer} km</strong>
                      </p>
                    </CardContent>
                  </Card>
                )}

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

      {/* Helyszín létrehozás form */}
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
          isAdmin={false}
          defaultLocationType={
            locationType === "start" ? "telephely" : "partner"
          }
          allowedLocationTypes={[
            "partner",
            "telephely",
            "töltőállomás",
            "bolt",
            "egyéb",
          ]}
          allowTypeSelection={true}
        />
      )}
    </>
  );
}

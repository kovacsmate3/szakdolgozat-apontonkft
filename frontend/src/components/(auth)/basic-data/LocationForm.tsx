"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { Resolver, useForm } from "react-hook-form";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Checkbox } from "@/components/ui/checkbox";
import { Location } from "@/lib/types";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { locationFormSchema } from "@/lib/schemas";
import { toast } from "sonner";
import { createLocation, updateLocation } from "@/server/locations";
import { useEffect, useMemo, useState } from "react";
import { LocationApiError } from "@/lib/errors";
import { publicSpaceTypes } from "@/lib/data/location-pages-data";
import { capitalize, getLocationTypeLabel } from "@/lib/functions";

interface LocationFormProps {
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  locationToEdit: Location | null;
  token: string;
  isAdmin: boolean;
  currentUserId: number;
  defaultLocationType?: string; // Az alapértelmezett helyszín típus
  allowedLocationTypes?: string[]; // Engedélyezett helyszín típusok
  allowTypeSelection?: boolean;
}

type LocationFormValues = {
  name: string;
  location_type: string;
  is_headquarter: boolean; // Explicit boolean típus
  country: string;
  postalcode: string;
  city: string;
  road_name: string;
  public_space_type: string;
  building_number: string;
};

export function LocationForm({
  isOpen,
  onOpenChange,
  locationToEdit,
  token,
  isAdmin,
  currentUserId,
  defaultLocationType = "egyéb", // Alapértelmezett típus
  allowedLocationTypes, // Engedélyezett típusok listája
  allowTypeSelection,
}: LocationFormProps) {
  const queryClient = useQueryClient();
  const [serverErrors, setServerErrors] = useState<Record<string, string[]>>(
    {}
  );

  // Figyelni kell a kiválasztott típust
  const [selectedType, setSelectedType] = useState<string>(
    locationToEdit?.location_type || defaultLocationType
  );

  const currentLocationType = useMemo(() => {
    if (locationToEdit) {
      return locationToEdit.location_type;
    }
    return selectedType;
  }, [locationToEdit, selectedType]);

  // A helyszín típus olvasható neve a dialógus címhez
  const locationTypeDisplayName = useMemo(() => {
    return getLocationTypeLabel(currentLocationType);
  }, [currentLocationType]);

  // Dialógus cím
  const dialogTitle = useMemo(() => {
    if (locationToEdit) {
      return `${getLocationTypeLabel(currentLocationType)} szerkesztése`;
    }
    return `Új ${locationTypeDisplayName} létrehozása`;
  }, [locationToEdit, currentLocationType, locationTypeDisplayName]);

  // Dialógus leírás
  const dialogDescription = useMemo(() => {
    if (locationToEdit) {
      return `Módosítsa a ${locationTypeDisplayName} adatait az alábbi űrlapon.`;
    }
    return `Adja meg az új ${locationTypeDisplayName} adatait az alábbi űrlapon.`;
  }, [locationToEdit, locationTypeDisplayName]);

  const defaultValues: LocationFormValues = {
    name: locationToEdit?.name || "",
    location_type: locationToEdit?.location_type || "egyéb",
    is_headquarter: locationToEdit?.is_headquarter || false,
    country: locationToEdit?.address?.country || "Magyarország",
    postalcode: locationToEdit?.address?.postalcode?.toString() || "",
    city: locationToEdit?.address?.city || "Budapest",
    road_name: locationToEdit?.address?.road_name || "",
    public_space_type: locationToEdit?.address?.public_space_type || "",
    building_number: locationToEdit?.address?.building_number || "",
  };

  // Form inicializálása a pontos típusokkal
  const form = useForm<LocationFormValues>({
    resolver: zodResolver(locationFormSchema) as Resolver<LocationFormValues>,
    defaultValues,
  });

  // Formot reseteljük, ha változik a szerkesztett helyszín
  useEffect(() => {
    if (isOpen) {
      // Ha szerkesztünk, megtartjuk az eredeti típust, különben az oldal által meghatározott típust használjuk
      const locationType =
        locationToEdit?.location_type ||
        (allowedLocationTypes?.length === 1
          ? allowedLocationTypes[0]
          : defaultLocationType);

      setSelectedType(locationType);

      form.reset({
        name: locationToEdit?.name || "",
        location_type: locationType,
        is_headquarter: locationToEdit?.is_headquarter || false,
        country: locationToEdit?.address?.country || "Magyarország",
        postalcode: locationToEdit?.address?.postalcode?.toString() || "",
        city: locationToEdit?.address?.city || "Budapest",
        road_name: locationToEdit?.address?.road_name || "",
        public_space_type: locationToEdit?.address?.public_space_type || "",
        building_number: locationToEdit?.address?.building_number || "",
      });
      setServerErrors({});
    }
  }, [form, isOpen, locationToEdit, defaultLocationType, allowedLocationTypes]);

  // Létrehozó mutáció
  const createMutation = useMutation({
    mutationFn: (data: LocationFormValues) =>
      createLocation({
        location: {
          ...data,
          postalcode: parseInt(data.postalcode), // Konvertálás számmá
        },
        token,
      }),
    onSuccess: (data) => {
      toast.success(
        `${capitalize(getLocationTypeLabel(currentLocationType))} sikeresen létrehozva!`,
        {
          description: data.message,
          duration: 4000,
        }
      );
      queryClient.invalidateQueries({ queryKey: ["locations"] });
      onOpenChange(false);
      form.reset(defaultValues);
    },
    onError: (error: Error) => {
      if (error instanceof LocationApiError) {
        setServerErrors(error.data.errors || {});
        toast.error(
          `Hiba történt a ${getLocationTypeLabel(currentLocationType)} létrehozása során`,
          {
            description: error.data.message,
            duration: 4000,
          }
        );
      } else {
        toast.error(`Hiba történt`, {
          description: `A ${getLocationTypeLabel(currentLocationType)} létrehozása sikertelen.`,
          duration: 4000,
        });
      }
    },
  });

  // Frissítő mutáció
  const updateMutation = useMutation({
    mutationFn: (data: LocationFormValues) =>
      updateLocation({
        id: locationToEdit!.id,
        location: {
          ...data,
          postalcode: parseInt(data.postalcode), // Konvertálás számmá
        },
        token,
      }),
    onSuccess: (data) => {
      toast.success(
        `${capitalize(getLocationTypeLabel(currentLocationType))} sikeresen frissítve!`,
        {
          description: data.message,
          duration: 4000,
        }
      );
      queryClient.invalidateQueries({ queryKey: ["locations"] });
      onOpenChange(false);
      form.reset(defaultValues);
    },
    onError: (error: Error) => {
      if (error instanceof LocationApiError) {
        setServerErrors(error.data.errors || {});
        toast.error(
          `Hiba történt a ${getLocationTypeLabel(currentLocationType)} frissítése során`,
          {
            description: error.data.message,
            duration: 4000,
          }
        );
      } else {
        toast.error(`Hiba történt`, {
          description: `A ${getLocationTypeLabel(currentLocationType)} frissítése sikertelen.`,
          duration: 4000,
        });
      }
    },
  });

  function onSubmit(data: LocationFormValues) {
    setServerErrors({});

    // Jogosultság ellenőrzés: csak admin vagy a helyszín tulajdonosa szerkeszthet
    if (
      locationToEdit &&
      locationToEdit.user_id &&
      locationToEdit.user_id !== currentUserId &&
      !isAdmin
    ) {
      toast.error("Nincs jogosultsága", {
        description:
          "Csak a helyszín létrehozója vagy adminisztrátor módosíthatja a helyszínt.",
        duration: 4000,
      });
      return;
    }

    // Telephelyeket csak admin hozhat létre/módosíthat
    if (data.location_type === "telephely" && !isAdmin) {
      toast.error("Nincs jogosultsága", {
        description:
          "Telephely típusú helyszínt csak adminisztrátor hozhat létre.",
        duration: 4000,
      });
      return;
    }

    if (locationToEdit) {
      // Helyszín módosítása
      updateMutation.mutate(data);
    } else {
      // Új helyszín létrehozása
      createMutation.mutate(data);
    }
  }

  const sortedPublicSpaceTypes = useMemo(() => {
    return [...publicSpaceTypes].sort((a, b) =>
      a.localeCompare(b, "hu", { sensitivity: "base" })
    );
  }, []);

  // Mentés/Létrehozás gomb szövege
  const submitButtonText = useMemo(() => {
    if (createMutation.isPending || updateMutation.isPending) {
      return "Feldolgozás...";
    }

    if (locationToEdit) {
      return "Mentés";
    }

    return `${capitalize(locationTypeDisplayName)} létrehozása`;
  }, [
    createMutation.isPending,
    updateMutation.isPending,
    locationToEdit,
    locationTypeDisplayName,
  ]);

  return (
    <Dialog open={isOpen} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{capitalize(dialogTitle)}</DialogTitle>
          <DialogDescription>{dialogDescription}</DialogDescription>
        </DialogHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
            <div className="grid grid-cols-1 gap-4 py-4">
              <div className="space-y-4">
                <h3 className="text-lg font-medium">
                  {capitalize(locationTypeDisplayName)} adatai
                </h3>
                <div className="grid grid-cols-2 gap-4">
                  <FormField
                    control={form.control}
                    name="name"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Neve</FormLabel>
                        <FormControl>
                          <Input {...field} placeholder="pl. Beluga Bay" />
                        </FormControl>
                        <FormMessage />
                        {serverErrors.name && (
                          <p className="text-sm font-medium text-destructive">
                            {serverErrors.name[0]}
                          </p>
                        )}
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="location_type"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Típusa</FormLabel>
                        <Select
                          onValueChange={(value) => {
                            // Itt: változás esetén frissítjük a state-et
                            setSelectedType(value);
                            field.onChange(value);
                          }}
                          defaultValue={field.value}
                          disabled={
                            !!locationToEdit ||
                            allowedLocationTypes?.length === 1 ||
                            !allowTypeSelection
                          }
                          value={field.value}
                        >
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue placeholder="Válasszon típust" />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {/* Ha van engedélyezett típusok listája, csak azokat jelenítjük meg */}
                            {allowedLocationTypes ? (
                              <>
                                {allowedLocationTypes.includes("telephely") &&
                                  isAdmin && (
                                    <SelectItem value="telephely">
                                      Telephely
                                    </SelectItem>
                                  )}
                                {allowedLocationTypes.includes("partner") && (
                                  <SelectItem value="partner">
                                    Partner
                                  </SelectItem>
                                )}
                                {allowedLocationTypes.includes(
                                  "töltőállomás"
                                ) && (
                                  <SelectItem value="töltőállomás">
                                    Töltőállomás
                                  </SelectItem>
                                )}
                                {allowedLocationTypes.includes("bolt") && (
                                  <SelectItem value="bolt">Bolt</SelectItem>
                                )}
                                {allowedLocationTypes.includes("egyéb") && (
                                  <SelectItem value="egyéb">Egyéb</SelectItem>
                                )}
                              </>
                            ) : (
                              <>
                                {/* Eredeti lista, ha nincs korlátozás */}
                                {isAdmin && (
                                  <SelectItem value="telephely">
                                    Telephely
                                  </SelectItem>
                                )}
                                <SelectItem value="partner">Partner</SelectItem>
                                <SelectItem value="töltőállomás">
                                  Töltőállomás
                                </SelectItem>
                                <SelectItem value="bolt">Bolt</SelectItem>
                                <SelectItem value="egyéb">Egyéb</SelectItem>
                              </>
                            )}
                          </SelectContent>
                        </Select>
                        <FormMessage />
                        {serverErrors.location_type && (
                          <p className="text-sm font-medium text-destructive">
                            {serverErrors.location_type[0]}
                          </p>
                        )}
                      </FormItem>
                    )}
                  />
                </div>

                {/* Székhely jelölő csak admin számára és csak telephely típusnál */}
                {isAdmin && allowedLocationTypes?.includes("telephely") && (
                  <FormField
                    control={form.control}
                    name="is_headquarter"
                    render={({ field }) => (
                      <FormItem className="flex flex-row items-start space-x-3 space-y-0 rounded-md border p-4">
                        <FormControl>
                          <Checkbox
                            checked={field.value}
                            onCheckedChange={field.onChange}
                          />
                        </FormControl>
                        <div className="space-y-1 leading-none">
                          <FormLabel>Székhely</FormLabel>
                          <FormDescription>
                            Jelölje be, ha ez az elsődleges székhely.
                          </FormDescription>
                          <div className="bg-amber-50 dark:bg-amber-950/30 p-4 mt-2 rounded-md border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm">
                            <p>
                              Figyelem: Ha bejelöli, a korábbi székhely státusz
                              más telephelyről automatikusan lekerül.
                            </p>
                          </div>
                        </div>
                      </FormItem>
                    )}
                  />
                )}

                <h3 className="text-lg font-medium mt-6">Cím adatok</h3>
                <div className="grid grid-cols-2 gap-4">
                  <FormField
                    control={form.control}
                    name="country"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Ország</FormLabel>
                        <FormControl>
                          <Input {...field} placeholder="Magyarország" />
                        </FormControl>
                        <FormMessage />
                        {serverErrors.country && (
                          <p className="text-sm font-medium text-destructive">
                            {serverErrors.country[0]}
                          </p>
                        )}
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="postalcode"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Irányítószám</FormLabel>
                        <FormControl>
                          <Input {...field} placeholder="1234" />
                        </FormControl>
                        <FormMessage />
                        {serverErrors.postalcode && (
                          <p className="text-sm font-medium text-destructive">
                            {serverErrors.postalcode[0]}
                          </p>
                        )}
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="city"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Város</FormLabel>
                        <FormControl>
                          <Input {...field} placeholder="Budapest" />
                        </FormControl>
                        <FormMessage />
                        {serverErrors.city && (
                          <p className="text-sm font-medium text-destructive">
                            {serverErrors.city[0]}
                          </p>
                        )}
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="road_name"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Közterület neve</FormLabel>
                        <FormControl>
                          <Input {...field} placeholder="pl. Napkirály" />
                        </FormControl>
                        <FormMessage />
                        {serverErrors.road_name && (
                          <p className="text-sm font-medium text-destructive">
                            {serverErrors.road_name[0]}
                          </p>
                        )}
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="public_space_type"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Közterület jellege</FormLabel>
                        <Select
                          onValueChange={field.onChange}
                          defaultValue={field.value}
                        >
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue placeholder="Válasszon típust" />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {sortedPublicSpaceTypes.map((type) => (
                              <SelectItem key={type} value={type}>
                                {type}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                        <FormMessage />
                        {serverErrors.public_space_type && (
                          <p className="text-sm font-medium text-destructive">
                            {serverErrors.public_space_type[0]}
                          </p>
                        )}
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="building_number"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Házszám</FormLabel>
                        <FormControl>
                          <Input {...field} placeholder="pl. 1/A." />
                        </FormControl>
                        <FormMessage />
                        {serverErrors.building_number && (
                          <p className="text-sm font-medium text-destructive">
                            {serverErrors.building_number[0]}
                          </p>
                        )}
                      </FormItem>
                    )}
                  />
                </div>
              </div>
            </div>

            <DialogFooter>
              <Button
                type="button"
                variant="outline"
                onClick={() => onOpenChange(false)}
              >
                Mégse
              </Button>
              <Button
                type="submit"
                disabled={createMutation.isPending || updateMutation.isPending}
              >
                {submitButtonText}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}

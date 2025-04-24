"use client";

import { useCallback, useMemo, useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Skeleton } from "@/components/ui/skeleton";
import { getLocations, deleteLocation } from "@/server/locations";
import { LocationCard } from "@/components/(auth)/basic-data/LocationCard";
import { LocationForm } from "@/components/(auth)/basic-data/LocationForm";
import { Button } from "@/components/ui/button";
import { Location } from "@/lib/types";
import { toast } from "sonner";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { DeleteDialog } from "@/components/delete-dialog";
import { capitalize, getLocationTypeLabel } from "@/lib/functions";
import { LocationApiError } from "@/lib/errors";

interface LocationsPageClientProps {
  token: string;
  isAdmin: boolean;
  userId: number;
  locationType?: string;
  title: string;
  selectOptions?: { value: string; label: string }[];
}

export default function LocationsPageClient({
  token,
  isAdmin,
  userId,
  locationType,
  title,
  selectOptions,
}: LocationsPageClientProps) {
  const queryClient = useQueryClient();
  const [locationToDelete, setLocationToDelete] = useState<Location | null>(
    null
  );
  const [locationToEdit, setLocationToEdit] = useState<Location | null>(null);
  const [formOpen, setFormOpen] = useState(false);
  const [currentType, setCurrentType] = useState<string | undefined>(
    locationType
  );

  // Oldalspecifikus helyszíntípusok meghatározása
  const allowedLocationTypes = useMemo(() => {
    if (!locationType) return undefined;

    // Az aktuális oldal által támogatott típusok kinyerése
    return locationType.split(",");
  }, [locationType]);

  const defaultLocationType = useMemo(() => {
    if (!allowedLocationTypes?.length) return "egyéb";

    // Ha szűrő van kiválasztva a lenyíló menüben, akkor azt használjuk
    if (currentType && currentType !== locationType) {
      // Ha a currentType több típust tartalmaz (vessző elválasztva)
      if (currentType.includes(",")) {
        // Első típust használjuk alapértelmezettként
        return currentType.split(",")[0];
      }
      return currentType;
    }

    return allowedLocationTypes[0]; // Az első engedélyezett típus
  }, [allowedLocationTypes, currentType, locationType]);

  // A szűrt típusok listájának kiszámítása
  const filteredAllowedTypes = useMemo(() => {
    // Ha nincs kiválasztott szűrő, vagy az megegyezik az oldal alap típusával,
    // akkor az eredeti engedélyezett típusok listáját használjuk
    if (!currentType || currentType === locationType) {
      return allowedLocationTypes;
    }

    // Ha van szűrő, akkor csak a szűrőben szereplő típusokat engedjük
    return currentType.split(",");
  }, [currentType, locationType, allowedLocationTypes]);

  // Gomb szövegének meghatározása az aktuális típus alapján
  const buttonText = useMemo(() => {
    // A szöveg alapja: "Új" + [típus neve]
    let typeText = "helyszín"; // Alapértelmezett szöveg

    // Ha van kiválasztott szűrő típus, akkor azt használjuk
    if (currentType && currentType !== locationType) {
      if (currentType.includes(",")) {
        // Ha több típus van vesszővel elválasztva, az elsőt használjuk
        const firstType = currentType.split(",")[0];
        typeText = getLocationTypeLabel(firstType);
      } else {
        typeText = getLocationTypeLabel(currentType);
      }
    }
    // Ha nincs kiválasztott szűrő, akkor az oldal alapértelmezett típusát használjuk
    else if (defaultLocationType) {
      typeText = getLocationTypeLabel(defaultLocationType);
    }

    return `+ Új ${typeText}`;
  }, [currentType, locationType, defaultLocationType]);

  const {
    data: locations,
    isLoading,
    error,
  } = useQuery({
    queryKey: ["locations", currentType],
    queryFn: () => getLocations(token, currentType),
  });

  // Törlés mutáció
  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteLocation({ id, token }),
    onSuccess: (data, variables) => {
      // Megkeressük a törölt helyszín típusát a lokális cache-ből
      const deletedLocation = locations?.find((loc) => loc.id === variables);
      const locationType = deletedLocation?.location_type || "helyszín";
      const typeLabel = getLocationTypeLabel(locationType);

      queryClient.invalidateQueries({ queryKey: ["locations"] });
      toast.success(`${capitalize(typeLabel)} sikeresen törölve!`, {
        duration: 4000,
        description: data.message || `A ${typeLabel} törölve lett.`,
      });
      setLocationToDelete(null);
    },
    onError: (error: Error) => {
      console.error("Delete error:", error);

      // Ellenőrizzük, hogy van-e response adat
      // A pontos hibaüzenet kinyerése a megfelelő típus szerint
      let errorMessage = "A törlés sikertelen.";

      if (error instanceof LocationApiError) {
        // Ha LocationApiError, akkor a hibaüzenet a data objektumban van
        errorMessage = error.data?.message || errorMessage;
      } else {
        // Ha valami más hiba, használjuk az alap üzenetet
        errorMessage = error.message || errorMessage;
      }

      toast.error(`Hiba történt`, {
        description: errorMessage,
        duration: 4000,
      });
      setLocationToDelete(null);
    },
  });

  // Új helyszín létrehozásának kezdeményezése
  const onCreateLocation = useCallback(() => {
    setLocationToEdit(null);
    setFormOpen(true);
  }, []);

  // Szerkesztés indítása
  const onEdit = useCallback((location: Location) => {
    setLocationToEdit(location);
    setFormOpen(true);
  }, []);

  // Törlés indítása
  const onDelete = useCallback((location: Location) => {
    setLocationToDelete(location);
  }, []);

  // Törlés megerősítése
  const handleConfirmDelete = useCallback(() => {
    if (locationToDelete?.id) {
      deleteMutation.mutate(locationToDelete.id);
    }
  }, [locationToDelete, deleteMutation]);

  // Helyszínek lista előkészítése
  const locationsList = useMemo(
    () =>
      locations?.map((location) => (
        <LocationCard
          key={location.id}
          location={location}
          isAdmin={isAdmin}
          currentUserId={userId}
          onEdit={onEdit}
          onDelete={onDelete}
        />
      )),
    [locations, isAdmin, userId, onEdit, onDelete]
  );

  // Ha több elemű a szűrő, vagy nincs szűrés
  const shouldAllowTypeSelection = useMemo(() => {
    if (!currentType || currentType === locationType) {
      return true;
    }

    // Ha van szűrés és vessző van benne (több típus)
    if (currentType.includes(",")) {
      return true;
    }

    // Egyéb esetben letiltjuk, mert egy adott típusra szűrünk
    return false;
  }, [currentType, locationType]);

  if (isLoading) {
    return (
      <div className="container mx-auto py-10">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-6">
          <h1 className="text-2xl font-semibold flex-shrink-0">{title}</h1>
          {selectOptions && (
            <Select disabled>
              <SelectTrigger className="w-full sm:w-[200px]">
                <SelectValue placeholder="Típus szűrése" />
              </SelectTrigger>
            </Select>
          )}
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {[...Array(6)].map((_, index) => (
            <Skeleton key={index} className="h-40 w-full" />
          ))}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto py-10">
        <h1 className="text-2xl font-semibold mb-6">Hiba történt</h1>
        <p>Nem sikerült betölteni a helyszíneket.</p>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-10">
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-6">
        <h1 className="text-2xl font-semibold flex-shrink-0">{title}</h1>
        <div className="flex flex-wrap items-center gap-2 w-full sm:w-auto">
          {selectOptions && (
            <Select
              value={currentType || ""}
              onValueChange={(v) => setCurrentType(v || undefined)}
            >
              <SelectTrigger className="w-full sm:w-auto max-w-xs">
                <SelectValue placeholder="Típus szűrése" />
              </SelectTrigger>
              <SelectContent>
                {selectOptions.map((opt) => (
                  <SelectItem key={opt.value} value={opt.value}>
                    {opt.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          )}
          <Button onClick={onCreateLocation} className="flex-shrink-0">
            {buttonText}
          </Button>
        </div>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {locations && locations.length > 0 ? (
          locationsList
        ) : (
          <p className="col-span-3 text-center py-10">
            Nincsenek megjeleníthető helyszínek.
          </p>
        )}
      </div>

      <DeleteDialog
        isOpen={!!locationToDelete}
        onOpenChange={(open) => {
          if (!open) setLocationToDelete(null);
        }}
        onConfirm={handleConfirmDelete}
        title={
          locationToDelete
            ? `${capitalize(getLocationTypeLabel(locationToDelete.location_type))} törlése`
            : "Törlés"
        }
        description={
          locationToDelete
            ? `Biztosan törölni szeretnéd a(z) ${locationToDelete.name} ${getLocationTypeLabel(locationToDelete.location_type)}-t?`
            : "Ez a művelet nem visszavonható."
        }
      />

      {/* Helyszín form */}
      <LocationForm
        token={token}
        locationToEdit={locationToEdit}
        isOpen={formOpen}
        onOpenChange={setFormOpen}
        currentUserId={userId}
        isAdmin={isAdmin}
        defaultLocationType={defaultLocationType}
        allowedLocationTypes={filteredAllowedTypes}
        allowTypeSelection={shouldAllowTypeSelection}
      />
    </div>
  );
}

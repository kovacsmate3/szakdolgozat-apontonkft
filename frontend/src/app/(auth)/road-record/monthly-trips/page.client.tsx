"use client";

import { format } from "date-fns";
import { hu } from "date-fns/locale";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Trip } from "@/lib/types";
import { ChevronLeft, FileText, Plus } from "lucide-react";
import { getCars } from "@/server/cars";
import { getLocations } from "@/server/locations";
import { getTravelPurposes } from "@/server/travel-purposes";
import { deleteTrip, getTrips } from "@/server/trips";
import { ExportTripsDialog } from "@/components/(auth)/road-record/monthly-trips/ExportTripsDialog";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useCalendar } from "@/hooks/use-calendar";
import { TripsCalendar } from "@/components/(auth)/road-record/monthly-trips/TripsCalendar";
import { useState } from "react";
import { TripForm } from "@/components/(auth)/road-record/monthly-trips/TripForm";
import { DeleteDialog } from "@/components/delete-dialog";
import { toast } from "sonner";
import { getUsers } from "@/server/users";
import { MonthYearNavigator } from "@/components/(auth)/road-record/MonthYearNavigator";

interface Props {
  token: string;
  userId: number;
  isAdmin: boolean;
}

export default function MonthlyTripsPageClient({
  token,
  userId,
  isAdmin,
}: Props) {
  const queryClient = useQueryClient();
  const [tripToEdit, setTripToEdit] = useState<Trip | null>(null);
  const [tripToDelete, setTripToDelete] = useState<Trip | null>(null);
  const [tripFormOpen, setTripFormOpen] = useState(false);

  const {
    selectedDate,
    selectedView,
    selectedDay,
    dataByDay: tripsByDay,
    isLoading: isLoadingTrips,
    isError: isErrorTrips,
    error,
    // Removed refreshData as it doesn't exist on the returned type
    navigateToPreviousMonth,
    navigateToNextMonth,
    handleDayClick,
    navigateBackToMonth,
    exportDialogOpen,
    setExportDialogOpen,
    handleExportClick,
    setSelectedDate,
    setSelectedDay,
    setSelectedView,
  } = useCalendar<Trip>({
    token,
    fetchData: getTrips,
    keyExtractor: (trip) => new Date(trip.start_time),
  });

  // Fetch all available cars
  const {
    data: cars,
    isLoading: isLoadingCars,
    isError: isErrorCars,
  } = useQuery({
    queryKey: ["cars", token],
    queryFn: getCars,
  });

  // Fetch all locations
  const { data: locations, isLoading: isLoadingLocations } = useQuery({
    queryKey: ["locations", token],
    queryFn: () => getLocations(token),
  });

  // Fetch travel purposes
  const { data: travelPurposes, isLoading: isLoadingTravelPurposes } = useQuery(
    {
      queryKey: ["travel-purposes", token],
      queryFn: getTravelPurposes,
    }
  );

  // Fetch users for association with trips
  const { data: users, isLoading: isLoadingUsers } = useQuery({
    queryKey: ["users", token],
    queryFn: getUsers,
  });

  const [initialDate, setInitialDate] = useState<Date | undefined>(undefined);

  // Trips deletion mutation
  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteTrip({ tripId: id, token }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["trips"] });
      queryClient.invalidateQueries({ queryKey: ["calendarData"] });
      toast.success("Utazás sikeresen törölve");
      setTripToDelete(null);
      // Instead of calling refreshData, invalidate the trips query
      queryClient.invalidateQueries({ queryKey: ["trips"] });
    },
    onError: (error) => {
      console.error("Törlési hiba:", error);
      toast.error("Hiba történt az utazás törlése során");
      setTripToDelete(null);
    },
  });

  const handleCreateTrip = (dateOrEvent?: Date | React.MouseEvent) => {
    setTripToEdit(null);

    // Ha date típusú paraméter érkezett
    if (dateOrEvent instanceof Date) {
      setInitialDate(dateOrEvent);
    } else {
      // Különben reset-eljük, hogy ne maradjon az előző
      setInitialDate(undefined);
    }

    setTripFormOpen(true);
  };

  // Utazás szerkesztése
  const handleEditTrip = (trip: Trip) => {
    setTripToEdit(trip);
    setTripFormOpen(true);
  };

  // Utazás törlése
  const handleDeleteTrip = (trip: Trip) => {
    setTripToDelete(trip);
  };

  // Törlés megerősítése
  const confirmDelete = () => {
    if (tripToDelete) {
      deleteMutation.mutate(tripToDelete.id);
    }
  };

  // Helyszínek újratöltése (új helyszín létrehozása után)
  const refreshLocations = () => {
    queryClient.invalidateQueries({ queryKey: ["locations"] });
  };

  // A formok adatbetöltésének állapota
  const isLoadingFormData =
    isLoadingCars ||
    isLoadingLocations ||
    isLoadingTravelPurposes ||
    isLoadingUsers;

  // For better error handling
  if (isErrorTrips) {
    console.error("Error loading trips:", error);
  }

  return (
    <div className="container mx-auto py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Havi utak</h1>

        <div className="flex flex-wrap gap-1 sm:gap-2">
          <Button
            variant="outline"
            size="sm"
            className="flex items-center gap-1 sm:gap-2 cursor-pointer"
            onClick={handleCreateTrip}
            disabled={isLoadingFormData}
          >
            <Plus className="h-4 w-4" />
            Új utazás
          </Button>
          {!isLoadingCars && !isErrorCars && cars && cars.length > 0 && (
            <Button
              variant="outline"
              onClick={handleExportClick}
              size="sm"
              className="flex items-center gap-1 sm:gap-2 cursor-pointer"
            >
              <FileText className="h-4 w-4" />
              Exportálás
            </Button>
          )}
        </div>
      </div>

      {isLoadingTrips ? (
        <div className="space-y-2">
          <Skeleton className="w-full h-16 rounded-lg" />
          <Skeleton className="w-full h-[700px] rounded-lg" />
        </div>
      ) : isErrorTrips ? (
        <div className="p-8 rounded-lg bg-red-50 border border-red-200">
          <h3 className="text-lg font-medium text-red-800 mb-2">
            Hiba történt az adatok betöltése során
          </h3>
          <p className="text-red-600">
            Kérjük, próbálja újra később vagy vegye fel a kapcsolatot a
            rendszergazdával.
          </p>
        </div>
      ) : (
        <Card className="shadow-md p-0">
          <CardHeader className="bg-muted/40 dark:bg-muted-dark/40 border-b pt-6 gap-0">
            <div className="flex justify-between items-center">
              {selectedView === "day" && selectedDay ? (
                <>
                  <Button
                    variant="ghost"
                    onClick={navigateBackToMonth}
                    className="text-lg"
                  >
                    <ChevronLeft className="mr-1 h-4 w-4" /> Vissza a havi
                    nézethez
                  </Button>
                  <div className="ml-auto flex items-center gap-2">
                    <CardTitle className="text-right">
                      {format(selectedDay, "yyyy. MMMM d., EEEE", {
                        locale: hu,
                      })}
                    </CardTitle>
                  </div>
                </>
              ) : (
                <MonthYearNavigator
                  selectedDate={selectedDate}
                  onDateChange={(date) => {
                    setSelectedDate(date);
                    setSelectedDay(null);
                    setSelectedView("month");
                  }}
                  onPreviousMonth={navigateToPreviousMonth}
                  onNextMonth={navigateToNextMonth}
                />
              )}
            </div>
          </CardHeader>
          <CardContent className="p-4">
            <TripsCalendar
              selectedDate={selectedDate}
              tripsByDay={tripsByDay}
              onDayClick={handleDayClick}
              view={selectedView}
              selectedDay={selectedDay}
              onEdit={handleEditTrip}
              onDelete={handleDeleteTrip}
              onCreateTrip={handleCreateTrip}
            />
          </CardContent>
        </Card>
      )}

      {/* Új utazás/szerkesztés form */}
      {!isLoadingFormData && (
        <TripForm
          token={token}
          tripToEdit={tripToEdit}
          isOpen={tripFormOpen}
          onOpenChange={setTripFormOpen}
          cars={cars || []}
          locations={locations || []}
          travelPurposes={travelPurposes || []}
          onLocationCreate={refreshLocations}
          userId={userId}
          isAdmin={isAdmin}
          users={users || []}
          initialDate={initialDate}
        />
      )}

      {/* Törlési dialógus */}
      <DeleteDialog
        isOpen={!!tripToDelete}
        onOpenChange={(open) => {
          if (!open) setTripToDelete(null);
        }}
        onConfirm={confirmDelete}
        title="Utazás törlése"
        description={
          tripToDelete
            ? `Biztosan törölni szeretné ezt az utazást? (${format(
                new Date(tripToDelete.start_time),
                "yyyy.MM.dd HH:mm"
              )}: ${tripToDelete.start_location?.name || ""} → ${
                tripToDelete.destination_location?.name || ""
              })`
            : "Ez a művelet nem visszavonható."
        }
      />

      {/* Export párbeszédablak */}
      {exportDialogOpen && (
        <ExportTripsDialog
          open={exportDialogOpen}
          onOpenChange={setExportDialogOpen}
          token={token}
          cars={cars || []}
          year={selectedDate.getFullYear()}
          month={selectedDate.getMonth() + 1}
        />
      )}
    </div>
  );
}

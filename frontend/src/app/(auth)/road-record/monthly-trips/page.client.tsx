"use client";

import { useState } from "react";
import { format } from "date-fns";
import { hu } from "date-fns/locale";
import { useQuery } from "@tanstack/react-query";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Trip } from "@/lib/types";
import { ChevronLeft, ChevronRight, FileText } from "lucide-react";
import { getTrips } from "@/server/trips";
import { CalendarView } from "@/components/(auth)/road-record/monthly-trips/CalendarView";
import { getCars } from "@/server/cars";
import { ExportTripsDialog } from "@/components/(auth)/road-record/monthly-trips/ExportTripsDialog";

interface Props {
  token: string;
}

export default function MonthlyTripsPageClient({ token }: Props) {
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [selectedView, setSelectedView] = useState<"month" | "day">("month");
  const [selectedDay, setSelectedDay] = useState<Date | null>(null);
  const [exportDialogOpen, setExportDialogOpen] = useState(false);

  // Calculate date range for the current month
  const firstDayOfMonth = new Date(
    selectedDate.getFullYear(),
    selectedDate.getMonth(),
    1
  );
  const lastDayOfMonth = new Date(
    selectedDate.getFullYear(),
    selectedDate.getMonth() + 1,
    0
  );

  // Format for API request
  const startDate = format(firstDayOfMonth, "yyyy-MM-dd");
  const endDate = format(lastDayOfMonth, "yyyy-MM-dd");

  // Fetch trips data for the current month
  const {
    data: trips,
    isLoading,
    isError,
    error,
  } = useQuery({
    queryKey: ["trips", token, startDate, endDate],
    queryFn: () => getTrips({ token, startDate, endDate }),
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

  // Group trips by day
  const tripsByDay = trips
    ? trips.reduce(
        (acc, trip) => {
          const tripDate = new Date(trip.start_time);
          const day = tripDate.getDate();

          if (!acc[day]) {
            acc[day] = [];
          }

          acc[day].push(trip);
          return acc;
        },
        {} as { [key: number]: Trip[] }
      )
    : {};

  const navigateToPreviousMonth = () => {
    setSelectedDate(
      new Date(selectedDate.getFullYear(), selectedDate.getMonth() - 1, 1)
    );
    setSelectedDay(null);
    setSelectedView("month");
  };

  const navigateToNextMonth = () => {
    setSelectedDate(
      new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 1)
    );
    setSelectedDay(null);
    setSelectedView("month");
  };

  const handleDayClick = (day: Date) => {
    setSelectedDay(day);
    setSelectedView("day");
  };

  const navigateBackToMonth = () => {
    setSelectedView("month");
    setSelectedDay(null);
  };

  const handleExportClick = () => {
    setExportDialogOpen(true);
  };

  // For better error handling
  if (isError) {
    console.error("Error loading trips:", error);
  }

  console.log(trips);

  return (
    <div className="container mx-auto py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Havi utak</h1>

        {!isLoadingCars && !isErrorCars && cars && cars.length > 0 && (
          <Button
            variant="outline"
            onClick={handleExportClick}
            className="flex items-center gap-2"
          >
            <FileText className="h-4 w-4" />
            Exportálás
          </Button>
        )}
      </div>

      {isLoading ? (
        <div className="space-y-2">
          <Skeleton className="w-full h-16 rounded-lg" />
          <Skeleton className="w-full h-[700px] rounded-lg" />
        </div>
      ) : isError ? (
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
                <>
                  <Button
                    variant="ghost"
                    onClick={navigateToPreviousMonth}
                    className="lg:text-lg"
                  >
                    <ChevronLeft className="mr-1 h-4 w-4" /> Előző hónap
                  </Button>
                  <CardTitle className="text-lg lg:text-xl">
                    {format(selectedDate, "yyyy. MMMM", { locale: hu })}
                  </CardTitle>
                  <Button
                    variant="ghost"
                    onClick={navigateToNextMonth}
                    className="lg:text-lg"
                  >
                    Következő hónap <ChevronRight className="ml-1 h-4 w-4" />
                  </Button>
                </>
              )}
            </div>
          </CardHeader>
          <CardContent className="p-4">
            <CalendarView
              selectedDate={selectedDate}
              tripsByDay={tripsByDay}
              onDayClick={handleDayClick}
              view={selectedView}
              selectedDay={selectedDay}
            />
          </CardContent>
        </Card>
      )}

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

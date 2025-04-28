"use client";

import { format } from "date-fns";
import { hu } from "date-fns/locale";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { getFuelExpenses, deleteFuelExpense } from "@/server/fuel-expenses";
import { FuelExpense } from "@/lib/types";
import { ChevronLeft, Plus } from "lucide-react";
import { useCalendar } from "@/hooks/use-calendar";
import { FuelExpensesCalendar } from "@/components/(auth)/road-record/refueling/FuelExpensesCalendar";
import { useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { getCars } from "@/server/cars";
import { getLocations } from "@/server/locations";
import { getTrips } from "@/server/trips";
import { FuelExpenseForm } from "@/components/(auth)/road-record/refueling/FuelExpenseForm";
import { DeleteDialog } from "@/components/delete-dialog";
import { toast } from "sonner";
import { getUsers } from "@/server/users";
import { MonthYearNavigator } from "@/components/(auth)/road-record/MonthYearNavigator";

interface Props {
  token: string;
  userId: number;
  isAdmin?: boolean;
}

export default function RefuelingsPageClient({
  token,
  userId,
  isAdmin,
}: Props) {
  const queryClient = useQueryClient();
  const [expenseToEdit, setExpenseToEdit] = useState<FuelExpense | null>(null);
  const [expenseToDelete, setExpenseToDelete] = useState<FuelExpense | null>(
    null
  );
  const [expenseFormOpen, setExpenseFormOpen] = useState(false);

  const {
    selectedDate,
    selectedView,
    selectedDay,
    dataByDay: expensesByDay,
    isLoading: isLoadingExpenses,
    isError: isErrorExpenses,
    navigateToPreviousMonth,
    navigateToNextMonth,
    handleDayClick,
    navigateBackToMonth,
    setSelectedDate,
    setSelectedDay,
    setSelectedView,
  } = useCalendar<FuelExpense>({
    token,
    fetchData: getFuelExpenses,
    keyExtractor: (expense) => new Date(expense.expense_date),
  });

  // Fetch all available cars
  const { data: cars, isLoading: isLoadingCars } = useQuery({
    queryKey: ["cars", token],
    queryFn: getCars,
  });

  // Fetch locations (fuel stations)
  const { data: locations, isLoading: isLoadingLocations } = useQuery({
    queryKey: ["locations", token],
    queryFn: () => getLocations(token, "töltőállomás"),
  });

  // Fetch trips for association with fuel expenses
  const { data: trips, isLoading: isLoadingTrips } = useQuery({
    queryKey: ["trips", token],
    queryFn: () => getTrips({ token }),
  });

  // Fetch users for association with fuel expenses
  const { data: users, isLoading: isLoadingUsers } = useQuery({
    queryKey: ["users", token],
    queryFn: getUsers,
  });

  const [initialDate, setInitialDate] = useState<Date | undefined>(undefined);

  // Fuel expense deletion mutation
  const deleteMutation = useMutation({
    mutationFn: (id: number) => deleteFuelExpense({ token, id }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["fuel-expenses"] });
      queryClient.invalidateQueries({ queryKey: ["calendarData"] });
      toast.success("Tankolás sikeresen törölve");
      setExpenseToDelete(null);
    },
    onError: (error) => {
      console.error("Törlési hiba:", error);
      toast.error("Hiba történt a tankolás törlése során");
      setExpenseToDelete(null);
    },
  });

  const handleCreateExpense = (dateOrEvent?: Date | React.MouseEvent) => {
    setExpenseToEdit(null);

    // Ha date típusú paraméter érkezett
    if (dateOrEvent instanceof Date) {
      setInitialDate(dateOrEvent);
    } else {
      // Különben reset-eljük, hogy ne maradjon az előző
      setInitialDate(undefined);
    }

    setExpenseFormOpen(true);
  };

  // Tankolás szerkesztése
  const handleEditExpense = (expense: FuelExpense) => {
    setExpenseToEdit(expense);
    setExpenseFormOpen(true);
  };

  // Tankolás törlése
  const handleDeleteExpense = (expense: FuelExpense) => {
    setExpenseToDelete(expense);
  };

  // Törlés megerősítése
  const confirmDelete = () => {
    if (expenseToDelete) {
      deleteMutation.mutate(expenseToDelete.id);
    }
  };

  // Töltőállomások újratöltése (új töltőállomás létrehozása után)
  const refreshLocations = () => {
    queryClient.invalidateQueries({ queryKey: ["locations"] });
  };

  // A formok adatbetöltésének állapota
  const isLoadingFormData =
    isLoadingCars || isLoadingLocations || isLoadingTrips || isLoadingUsers;

  return (
    <div className="container mx-auto py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Tankolások/Töltések</h1>

        <div className="flex flex-wrap gap-1 sm:gap-2">
          <Button
            variant="outline"
            size="sm"
            className="flex items-center gap-1 sm:gap-2 cursor-pointer"
            onClick={handleCreateExpense}
            disabled={isLoadingFormData}
          >
            <Plus className="h-4 w-4" />
            Új tankolás
          </Button>
        </div>
      </div>

      {isLoadingExpenses ? (
        <div className="space-y-2">
          <Skeleton className="w-full h-16 rounded-lg" />
          <Skeleton className="w-full h-[700px] rounded-lg" />
        </div>
      ) : isErrorExpenses ? (
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
            <FuelExpensesCalendar
              selectedDate={selectedDate}
              expensesByDay={expensesByDay}
              onDayClick={handleDayClick}
              view={selectedView}
              selectedDay={selectedDay}
              onEdit={handleEditExpense}
              onDelete={handleDeleteExpense}
              onCreateExpense={handleCreateExpense}
            />
          </CardContent>
        </Card>
      )}

      {/* Új tankolás/szerkesztés form */}
      {!isLoadingFormData && (
        <FuelExpenseForm
          token={token}
          expenseToEdit={expenseToEdit}
          isOpen={expenseFormOpen}
          onOpenChange={setExpenseFormOpen}
          cars={cars || []}
          locations={locations || []}
          trips={trips || []}
          users={users || []}
          onLocationCreate={refreshLocations}
          userId={userId}
          isAdmin={isAdmin}
          initialDate={initialDate}
        />
      )}

      {/* Törlési dialógus */}
      <DeleteDialog
        isOpen={!!expenseToDelete}
        onOpenChange={(open) => {
          if (!open) setExpenseToDelete(null);
        }}
        onConfirm={confirmDelete}
        title="Tankolás törlése"
        description={
          expenseToDelete
            ? `Biztosan törölni szeretné ezt a tankolást? (${format(
                new Date(expenseToDelete.expense_date),
                "yyyy.MM.dd HH:mm"
              )}: ${expenseToDelete.fuel_quantity} liter - ${expenseToDelete.amount} Ft)`
            : "Ez a művelet nem visszavonható."
        }
      />
    </div>
  );
}

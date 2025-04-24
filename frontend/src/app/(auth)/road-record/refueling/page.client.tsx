"use client";

import { format } from "date-fns";
import { hu } from "date-fns/locale";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { getFuelExpenses } from "@/server/fuel-expenses";
import { FuelExpense } from "@/lib/types";
import { ChevronLeft, ChevronRight, FileText, Plus } from "lucide-react";
import { useCalendar } from "@/hooks/use-calendar";
import { FuelExpensesCalendar } from "@/components/(auth)/road-record/refueling/FuelExpensesCalendar";

interface Props {
  token: string;
}

export default function MonthlyFuelExpensesPageClient({ token }: Props) {
  const {
    selectedDate,
    selectedView,
    selectedDay,
    dataByDay: expensesByDay,
    isLoading,
    isError,
    navigateToPreviousMonth,
    navigateToNextMonth,
    handleDayClick,
    navigateBackToMonth,
  } = useCalendar<FuelExpense>({
    token,
    fetchData: getFuelExpenses,
    keyExtractor: (expense) => new Date(expense.expense_date),
  });

  return (
    <div className="container mx-auto py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Tankolások/Töltések</h1>

        <div className="flex flex-wrap gap-1 sm:gap-2">
          <Button
            variant="outline"
            size="sm"
            className="flex items-center gap-1 sm:gap-2 cursor-pointer"
          >
            <Plus className="h-4 w-4" />
            Új tankolás
          </Button>
          <Button
            variant="outline"
            size="sm"
            className="flex items-center gap-1 sm:gap-2 cursor-pointer"
          >
            <FileText className="h-4 w-4" />
            Exportálás
          </Button>
        </div>
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
            <FuelExpensesCalendar
              selectedDate={selectedDate}
              expensesByDay={expensesByDay}
              onDayClick={handleDayClick}
              view={selectedView}
              selectedDay={selectedDay}
            />
          </CardContent>
        </Card>
      )}
    </div>
  );
}

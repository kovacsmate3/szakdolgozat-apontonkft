"use client";

import { format } from "date-fns";
import { hu } from "date-fns/locale";
import { FuelExpense } from "@/lib/types";
import {
  CalendarClock,
  Droplets,
  Car,
  MapPin,
  Clock,
  Pencil,
  Trash2,
  Plus,
  Info,
  Calendar,
  ArrowRight,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { formatDistance, formatHUF, getFullAddress } from "@/lib/functions";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";

interface FuelExpenseDayDetailProps {
  day: Date;
  expenses: FuelExpense[];
  onEdit?: (expense: FuelExpense) => void;
  onDelete?: (expense: FuelExpense) => void;
  onCreateExpense?: (defaultDate?: Date) => void;
}

export function FuelExpenseDayDetail({
  day,
  expenses,
  onEdit,
  onDelete,
  onCreateExpense,
}: FuelExpenseDayDetailProps) {
  const totalFuelQuantity = expenses.reduce(
    (total, expense) => total + expense.fuel_quantity,
    0
  );

  const totalAmount = expenses.reduce(
    (total, expense) => total + expense.amount,
    0
  );

  // Sort expenses by expense_date
  const sortedExpenses = [...expenses].sort(
    (a, b) =>
      new Date(a.expense_date).getTime() - new Date(b.expense_date).getTime()
  );

  if (expenses.length === 0) {
    return (
      <div className="p-6 flex flex-col items-center justify-center space-y-4 min-h-[300px] border rounded-md bg-muted/10">
        <CalendarClock className="h-12 w-12 text-muted-foreground" />
        <p className="text-muted-foreground text-center max-w-md">
          Ezen a napon ({format(day, "yyyy. MMMM d.", { locale: hu })}) nem
          található rögzített tankolási adat.
        </p>
        <Button
          className="mt-2"
          onClick={() => {
            // Új Date objektum létrehozása az adott napra, 12:00 órakor
            const defaultDate = new Date(day);
            defaultDate.setHours(12);
            defaultDate.setMinutes(0);
            defaultDate.setSeconds(0);
            onCreateExpense?.(defaultDate);
          }}
        >
          <Plus className="mr-2 h-4 w-4" />
          Tankolás hozzáadása
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h3 className="text-lg font-medium">
            {format(day, "yyyy. MMMM d., EEEE", { locale: hu })}
          </h3>
          <p className="text-sm text-muted-foreground">
            Összesen: {expenses.length} tankolás, {totalFuelQuantity.toFixed(2)}{" "}
            liter, {formatHUF(totalAmount)}
          </p>
        </div>
        <Button
          className="bg-primary text-primary-foreground"
          onClick={() => {
            // Új Date objektum létrehozása az adott napra, 12:00 órakor
            const defaultDate = new Date(day);
            defaultDate.setHours(12);
            defaultDate.setMinutes(0);
            defaultDate.setSeconds(0);
            onCreateExpense?.(defaultDate);
          }}
        >
          <Plus className="mr-2 h-4 w-4" />
          Tankolás hozzáadása
        </Button>
      </div>

      <Card className="overflow-hidden">
        <CardHeader className="bg-muted/30 py-3">
          <CardTitle className="text-base font-medium">
            Napi tankolások
          </CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-[120px]">Időpont</TableHead>
                  <TableHead>Helyszín</TableHead>
                  <TableHead>Mennyiség</TableHead>
                  <TableHead>Összeg</TableHead>
                  <TableHead>Km óra</TableHead>
                  <TableHead>Jármű</TableHead>
                  <TableHead>Kapcsolódó út</TableHead>
                  <TableHead className="text-center">Műveletek</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {sortedExpenses.map((expense) => (
                  <TableRow key={expense.id}>
                    <TableCell className="font-medium whitespace-nowrap">
                      <div className="flex items-center gap-1">
                        <Clock className="h-3 w-3 text-muted-foreground" />
                        <span>
                          {format(new Date(expense.expense_date), "HH:mm")}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1">
                        <MapPin className="h-3 w-3 text-muted-foreground" />
                        <span
                          className="max-w-[150px] truncate"
                          title={expense.location?.name || "Ismeretlen"}
                        >
                          {expense.location?.name || "Ismeretlen"}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1">
                        <Droplets className="h-3 w-3 text-muted-foreground" />
                        <span>{expense.fuel_quantity.toFixed(2)} liter</span>
                      </div>
                    </TableCell>
                    <TableCell>{formatHUF(expense.amount)}</TableCell>
                    <TableCell>{expense.odometer} km</TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1">
                        <Car className="h-3 w-3 text-muted-foreground" />
                        <span
                          className="max-w-[150px] truncate"
                          title={
                            expense.car
                              ? `${expense.car.model} (${expense.car.license_plate})`
                              : "Ismeretlen"
                          }
                        >
                          {expense.car
                            ? `${expense.car.model} (${expense.car.license_plate})`
                            : "Ismeretlen"}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell>
                      {expense.trip ? (
                        <Popover>
                          <PopoverTrigger asChild>
                            <Button
                              variant="outline"
                              size="sm"
                              className="h-7 gap-1 px-2"
                            >
                              <Info className="h-3.5 w-3.5" />
                              <span>Útadatok</span>
                            </Button>
                          </PopoverTrigger>
                          <PopoverContent className="w-80" align="start">
                            <div className="space-y-3">
                              <h4 className="font-medium text-sm">
                                Kapcsolódó út részletei
                              </h4>

                              <div className="space-y-2 text-sm">
                                <div className="flex justify-between gap-2">
                                  <div className="flex items-center gap-1 text-muted-foreground">
                                    <Calendar className="h-3.5 w-3.5" />
                                    <span>Időpont:</span>
                                  </div>
                                  <div className="font-medium">
                                    {format(
                                      new Date(expense.trip.start_time),
                                      "yyyy.MM.dd HH:mm",
                                      { locale: hu }
                                    )}
                                  </div>
                                </div>

                                <div className="flex gap-1 items-center border-b pb-1">
                                  <div className="text-muted-foreground text-xs w-24">
                                    Útvonal:
                                  </div>
                                  <div className="flex items-center gap-1 text-xs">
                                    <span className="font-medium">
                                      {expense.trip.start_location?.address
                                        ? getFullAddress(
                                            expense.trip.start_location.address
                                          )
                                        : expense.trip.start_location?.name ||
                                          "Ismeretlen"}
                                    </span>
                                    <ArrowRight className="h-3 w-3 mx-1 text-muted-foreground" />
                                    <span className="font-medium">
                                      {expense.trip.destination_location
                                        ?.address
                                        ? getFullAddress(
                                            expense.trip.destination_location
                                              .address
                                          )
                                        : expense.trip.destination_location
                                            ?.name || "Ismeretlen"}
                                    </span>
                                  </div>
                                </div>

                                <div className="grid grid-cols-2 gap-x-2 gap-y-1 text-sm pt-1">
                                  <div className="text-muted-foreground">
                                    Távolság:
                                  </div>
                                  <div className="text-right">
                                    {formatDistance(
                                      expense.trip.actual_distance || 0
                                    )}
                                  </div>

                                  <div className="text-muted-foreground">
                                    Utazási cél:
                                  </div>
                                  <div className="text-right">
                                    {expense.trip.travel_purpose
                                      ?.travel_purpose || "Nincs megadva"}
                                  </div>

                                  <div className="text-muted-foreground">
                                    Km. óra (kezdő):
                                  </div>
                                  <div className="text-right">
                                    {expense.trip.start_odometer
                                      ? `${expense.trip.start_odometer} km`
                                      : "Nincs megadva"}
                                  </div>

                                  <div className="text-muted-foreground">
                                    Km. óra (záró):
                                  </div>
                                  <div className="text-right">
                                    {expense.trip.end_odometer
                                      ? `${expense.trip.end_odometer} km`
                                      : "Nincs megadva"}
                                  </div>
                                </div>
                              </div>
                            </div>
                          </PopoverContent>
                        </Popover>
                      ) : (
                        <Badge variant="outline" className="text-xs">
                          Nincs kapcsolt út
                        </Badge>
                      )}
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-1 whitespace-nowrap">
                        {onEdit && (
                          <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => onEdit(expense)}
                          >
                            <Pencil className="h-4 w-4 mr-1" />
                            <span className="hidden sm:inline">
                              Szerkesztés
                            </span>
                          </Button>
                        )}
                        {onDelete && (
                          <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            onClick={() => onDelete(expense)}
                          >
                            <Trash2 className="h-4 w-4 mr-1" />
                            <span className="hidden sm:inline">Törlés</span>
                          </Button>
                        )}
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

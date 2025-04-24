"use client";

import { FuelExpense } from "@/lib/types";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { roundToTwoDecimals } from "@/lib/functions";
import { Droplets } from "lucide-react";
import { GiCash } from "react-icons/gi";

interface FuelExpenseDayCellProps {
  day: Date;
  expenses: FuelExpense[];
  isToday: boolean;
  isCurrentMonth: boolean;
}

export function FuelExpenseDayCell({
  day,
  expenses,
  isToday,
  isCurrentMonth,
}: FuelExpenseDayCellProps) {
  const dayNumber = day.getDate();
  const expenseCount = expenses.length;
  const isWeekend = [0, 6].includes(day.getDay());

  // Számítsd ki az összes tankolt üzemanyag mennyiséget és költséget
  const totalFuelQuantity = expenses.reduce(
    (total, expense) => total + expense.fuel_quantity,
    0
  );
  const totalAmount = expenses.reduce(
    (total, expense) => total + expense.amount,
    0
  );

  const displayExpenses = expenses.slice(0, 2);
  const hasMoreExpenses = expenses.length > 2;

  return (
    <div
      className={cn(
        "p-2 h-36 overflow-y-auto relative cursor-pointer transition-colors",
        !isCurrentMonth && "opacity-60 disabled:pointer-events-none",
        isToday && "ring-2 ring-primary",
        isWeekend
          ? "bg-red-50 hover:bg-red-200 dark:bg-red-800 dark:hover:bg-red-700"
          : "hover:bg-muted/50 dark:hover:bg-muted-dark/50"
      )}
    >
      {/* Day number + expense count */}
      <div className="flex justify-between items-center mb-2 border-b">
        <Button
          variant={isToday ? "default" : "ghost"}
          size="sm"
          className={cn(
            "h-8 w-8 p-0 rounded-full font-medium cursor-pointer pointer-events-none",
            isToday && "text-primary-foreground",
            !isCurrentMonth && "text-muted-foreground"
          )}
        >
          {dayNumber}
        </Button>
        {expenseCount > 0 && (
          <span className="hidden md:inline text-xs text-muted-foreground font-medium">
            {expenseCount} tankolás
          </span>
        )}
      </div>

      {/* XS‑es összefoglaló ikon+darabszám */}
      {expenseCount > 0 && (
        <>
          <div className="flex md:hidden items-center gap-1">
            <Droplets className="size-4 text-muted-foreground" />
            <span className="text-xs font-medium">
              {roundToTwoDecimals(totalFuelQuantity)} L
            </span>
          </div>
          <div className="flex md:hidden items-center gap-1">
            <GiCash className="size-4 text-muted-foreground" />
            <span className="text-xs font-medium">{totalAmount} Ft</span>
          </div>
        </>
      )}

      {/* Expense details for larger screens */}
      <div className="hidden md:block space-y-1">
        {displayExpenses.map((expense) => (
          <div key={expense.id} className="truncate text-xs font-medium">
            {expense.location?.name || "?"}: {expense.fuel_quantity} L (
            {expense.amount} Ft)
          </div>
        ))}
        {hasMoreExpenses && (
          <div className="text-xs text-muted-foreground truncate">
            +{expenseCount - 2} további…
          </div>
        )}

        {/* No expenses message */}
        {expenseCount === 0 && isCurrentMonth && (
          <div className="h-full flex items-center justify-center">
            <span className="text-xs text-muted-foreground">
              Nincs tankolás
            </span>
          </div>
        )}
      </div>
    </div>
  );
}

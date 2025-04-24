"use client";

import { FuelExpense } from "@/lib/types";
import { FuelExpenseDayCell } from "./FuelExpenseDayCell";
import { FuelExpenseDayDetail } from "./FuelExpenseDayDetail";
import { Calendar } from "@/components/full-calendar";

interface FuelExpensesCalendarProps {
  selectedDate: Date;
  expensesByDay: { [key: number]: FuelExpense[] };
  onDayClick: (day: Date) => void;
  view: "month" | "day";
  selectedDay: Date | null;
}

export function FuelExpensesCalendar({
  selectedDate,
  expensesByDay,
  onDayClick,
  view,
  selectedDay,
}: FuelExpensesCalendarProps) {
  return (
    <Calendar<FuelExpense>
      selectedDate={selectedDate}
      dataByDay={expensesByDay}
      renderDayCell={(day, expenses, isToday, isCurrentMonth) => (
        <FuelExpenseDayCell
          day={day}
          expenses={expenses}
          isToday={isToday}
          isCurrentMonth={isCurrentMonth}
        />
      )}
      renderDayDetail={(day, expenses) => (
        <FuelExpenseDayDetail day={day} expenses={expenses} />
      )}
      onDayClick={onDayClick}
      view={view}
      selectedDay={selectedDay}
    />
  );
}

"use client";

import { Calendar } from "@/components/full-calendar";
import { FuelExpense } from "@/lib/types";
import { FuelExpenseDayDetail } from "./FuelExpenseDayDetail";
import { FuelExpenseDayCell } from "./FuelExpenseDayCell";

interface FuelExpensesCalendarProps {
  selectedDate: Date;
  expensesByDay: { [key: number]: FuelExpense[] };
  onDayClick: (date: Date) => void;
  view: "month" | "day";
  selectedDay: Date | null;
  onEdit: (expense: FuelExpense) => void;
  onDelete: (expense: FuelExpense) => void;
  onCreateExpense: () => void;
}

export function FuelExpensesCalendar({
  selectedDate,
  expensesByDay,
  onDayClick,
  view,
  selectedDay,
  onEdit,
  onDelete,
  onCreateExpense,
}: FuelExpensesCalendarProps) {
  // Render a single day cell in month view
  const renderDayCell = (
    day: Date,
    items: FuelExpense[],
    isToday: boolean,
    isCurrentMonth: boolean
  ) => {
    return (
      <FuelExpenseDayCell
        day={day}
        expenses={items}
        isToday={isToday}
        isCurrentMonth={isCurrentMonth}
      />
    );
  };

  // Render the day detail view
  const renderDayDetail = (day: Date, items: FuelExpense[]) => {
    return (
      <FuelExpenseDayDetail
        day={day}
        expenses={items}
        onEdit={onEdit}
        onDelete={onDelete}
        onCreateExpense={onCreateExpense}
      />
    );
  };

  return (
    <Calendar
      selectedDate={selectedDate}
      dataByDay={expensesByDay}
      renderDayCell={renderDayCell}
      renderDayDetail={renderDayDetail}
      onDayClick={onDayClick}
      view={view}
      selectedDay={selectedDay}
    />
  );
}

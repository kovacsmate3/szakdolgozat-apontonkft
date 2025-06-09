"use client";

import { getDay, isToday, startOfMonth, isSameMonth } from "date-fns";
import { cn } from "@/lib/utils";

interface CalendarProps<T> {
  selectedDate: Date;
  dataByDay: { [key: number]: T[] };
  renderDayCell: (
    day: Date,
    items: T[],
    isToday: boolean,
    isCurrentMonth: boolean
  ) => React.ReactNode;
  renderDayDetail: (day: Date, items: T[]) => React.ReactNode;
  onDayClick: (day: Date) => void;
  view: "month" | "day";
  selectedDay: Date | null;
}

export function Calendar<T>({
  selectedDate,
  dataByDay,
  renderDayCell,
  renderDayDetail,
  onDayClick,
  view,
  selectedDay,
}: CalendarProps<T>) {
  // 1) If we're in "day" mode, just show the detail panel:
  if (view === "day" && selectedDay) {
    return renderDayDetail(selectedDay, dataByDay[selectedDay.getDate()] || []);
  }

  // 2) Otherwise, build the month grid:

  // How many days this month has
  const daysInMonth = new Date(
    selectedDate.getFullYear(),
    selectedDate.getMonth() + 1,
    0
  ).getDate();

  // On which weekday the 1st of this month falls (0 = Sunday)
  const firstOfMonth = startOfMonth(selectedDate);
  const rawWeekday = getDay(firstOfMonth);

  // In Hungary weeks start on Monday=1…Sunday=7, so shift:
  const offset = rawWeekday === 0 ? 6 : rawWeekday - 1;

  // Build an array of either `null` (empty cell) or a Date for each day:
  const days: (Date | null)[] = [];

  // leading nulls:
  for (let i = 0; i < offset; i++) {
    days.push(null);
  }
  // actual days:
  for (let d = 1; d <= daysInMonth; d++) {
    days.push(new Date(selectedDate.getFullYear(), selectedDate.getMonth(), d));
  }

  // trailing nulls, hogy a napok száma mindig 7-re osztható legyen
  const remainder = days.length % 7;
  if (remainder !== 0) {
    const trailing = 7 - remainder;
    for (let i = 0; i < trailing; i++) {
      days.push(null);
    }
  }

  // Weekday labels, starting Monday
  const weekdays = [
    "Hétfő",
    "Kedd",
    "Szerda",
    "Csütörtök",
    "Péntek",
    "Szombat",
    "Vasárnap",
  ];

  return (
    <div className="relative w-full overflow-hidden rounded-md shadow-md">
      <div className="grid grid-cols-7 border border-muted dark:border-muted-dark">
        {weekdays.map((wd) => (
          <div
            key={wd}
            className="px-2 text-center font-medium text-foreground border-b bg-muted/40 dark:bg-muted-dark/40 border-l border-r border-muted dark:border-muted-dark p-4 text-xs sm:text-base"
          >
            {wd}
          </div>
        ))}

        {days.map((cell, idx) => {
          // empty slot before/after month
          if (!cell) {
            return (
              <div
                key={`empty-${idx}`}
                className={cn(
                  "border-b border-l border-r border-muted dark:border-muted-dark transition-colors",
                  Math.floor(idx / 7) === Math.floor((days.length - 1) / 7) &&
                    "border-b"
                )}
              />
            );
          }

          const dayNum = cell.getDate();
          const isCurrent = isSameMonth(cell, selectedDate);
          const today = isToday(cell);
          const items = dataByDay[dayNum] || [];

          return (
            <div
              key={cell.toISOString()}
              className={cn(
                "border-b border-l border-r border-muted dark:border-muted-dark transition-colors",
                Math.floor(idx / 7) === Math.floor((days.length - 1) / 7) &&
                  "border-b"
              )}
              onClick={() => onDayClick(cell)}
            >
              {renderDayCell(cell, items, today, isCurrent)}
            </div>
          );
        })}
      </div>
    </div>
  );
}

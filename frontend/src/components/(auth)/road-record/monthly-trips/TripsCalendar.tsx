"use client";

import { Calendar } from "@/components/full-calendar";
import { Trip } from "@/lib/types";
import { TripsDayCell } from "./TripsDayCell";
import { TripsDayDetail } from "./TripsDayDetail";

interface TripsCalendarProps {
  selectedDate: Date;
  tripsByDay: { [key: number]: Trip[] };
  onDayClick: (date: Date) => void;
  view: "month" | "day";
  selectedDay: Date | null;
  onEdit: (trip: Trip) => void;
  onDelete: (trip: Trip) => void;
  onCreateTrip: () => void;
}

export function TripsCalendar({
  selectedDate,
  tripsByDay,
  onDayClick,
  view,
  selectedDay,
  onEdit,
  onDelete,
  onCreateTrip,
}: TripsCalendarProps) {
  // Render a single day cell in month view
  const renderDayCell = (
    day: Date,
    items: Trip[],
    isToday: boolean,
    isCurrentMonth: boolean
  ) => {
    return (
      <TripsDayCell
        day={day}
        trips={items}
        isToday={isToday}
        isCurrentMonth={isCurrentMonth}
        onClick={() => {}} // The parent Calendar component will handle this
      />
    );
  };

  // Render the day detail view
  const renderDayDetail = (day: Date, items: Trip[]) => {
    return (
      <TripsDayDetail
        day={day}
        trips={items}
        onEdit={onEdit}
        onDelete={onDelete}
        onCreateTrip={onCreateTrip}
      />
    );
  };

  return (
    <Calendar
      selectedDate={selectedDate}
      dataByDay={tripsByDay}
      renderDayCell={renderDayCell}
      renderDayDetail={renderDayDetail}
      onDayClick={onDayClick}
      view={view}
      selectedDay={selectedDay}
    />
  );
}

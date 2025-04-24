"use client";

import { Calendar } from "@/components/full-calendar";
import { Trip } from "@/lib/types";
import { TripsDayCell } from "./TripsDayCell";
import { TripsDayDetail } from "./TripsDayDetail";

interface TripsCalendarProps {
  selectedDate: Date;
  tripsByDay: { [key: number]: Trip[] };
  onDayClick: (day: Date) => void;
  view: "month" | "day";
  selectedDay: Date | null;
}

export function TripsCalendar({
  selectedDate,
  tripsByDay,
  onDayClick,
  view,
  selectedDay,
}: TripsCalendarProps) {
  return (
    <Calendar<Trip>
      selectedDate={selectedDate}
      dataByDay={tripsByDay}
      renderDayCell={(day, trips, isToday, isCurrentMonth) => (
        <TripsDayCell
          day={day}
          trips={trips}
          isToday={isToday}
          onClick={() => {}} // A GenericCalendar div-jének onClick eseménye fogja kezelni
          isCurrentMonth={isCurrentMonth}
        />
      )}
      renderDayDetail={(day, trips) => (
        <TripsDayDetail day={day} trips={trips} />
      )}
      onDayClick={onDayClick}
      view={view}
      selectedDay={selectedDay}
    />
  );
}

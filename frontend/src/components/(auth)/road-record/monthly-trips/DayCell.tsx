"use client";

import { Trip } from "@/lib/types";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { calculateTotalDistance, roundToTwoDecimals } from "@/lib/functions";
import { Car } from "lucide-react";
import { FaRoad } from "react-icons/fa";

interface DayCellProps {
  day: Date;
  trips: Trip[];
  isToday: boolean;
  onClick: () => void;
  isCurrentMonth: boolean;
}

export function DayCell({
  day,
  trips,
  isToday,
  onClick,
  isCurrentMonth,
}: DayCellProps) {
  const dayNumber = day.getDate();
  const rawDistance = calculateTotalDistance(trips);
  const totalDistance = roundToTwoDecimals(rawDistance);
  const tripCount = trips.length;
  const isWeekend = [0, 6].includes(day.getDay());

  const displayTrips = trips.slice(0, 2);
  const hasMoreTrips = trips.length > 2;

  return (
    <div
      onClick={onClick}
      className={cn(
        "p-2 h-36 overflow-y-auto relative cursor-pointer transition-colors",
        !isCurrentMonth && "opacity-60 disabled:pointer-events-none",
        isToday && "ring-2 ring-primary",
        isWeekend
          ? "bg-red-50 hover:bg-red-200 dark:bg-red-800 dark:hover:bg-red-700"
          : "hover:bg-muted/50 dark:hover:bg-muted-dark/50"
      )}
    >
      {/* Day number + total km */}
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
        {tripCount > 0 && (
          <span className="hidden md:inline text-xs text-muted-foreground font-medium">
            {tripCount} út — {totalDistance} km
          </span>
        )}
      </div>
      {/* XS‑es összefoglaló ikon+darabszám */}
      {tripCount > 0 && (
        <>
          <div className="flex md:hidden items-center gap-1">
            <Car className="size-4 text-muted-foreground" />
            <span className="text-xs font-medium">{tripCount}</span>
          </div>
          <div className="flex md:hidden items-center gap-1">
            <FaRoad className="size-4 text-muted-foreground" />
            <span className="text-xs font-medium">{totalDistance} km</span>
          </div>
        </>
      )}
      {/* Locations preview */}
      <div className="hidden md:block space-y-1">
        {displayTrips.map((trip) => (
          <div key={trip.id} className="truncate text-xs font-medium">
            {trip.start_location?.name || "?"} →{" "}
            {trip.destination_location?.name || "?"}
          </div>
        ))}
        {hasMoreTrips && (
          <div className="text-xs text-muted-foreground truncate">
            +{tripCount - 2} további…
          </div>
        )}

        {/* No trips message */}
        {tripCount === 0 && isCurrentMonth && (
          <div className="h-full flex items-center justify-center">
            <span className="text-xs text-muted-foreground">Nincs utazás</span>
          </div>
        )}
      </div>
    </div>
  );
}

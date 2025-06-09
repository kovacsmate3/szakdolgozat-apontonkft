"use client";

import { useState } from "react";
import { format } from "date-fns";
import { useQuery } from "@tanstack/react-query";
import { Trip, FuelExpense } from "@/lib/types";

// Típus helyett megközelítés típusőrökkel
type FetchDataFunction<T> = (params: {
  token: string;
  startDate: string;
  endDate: string;
  [key: string]: unknown;
}) => Promise<T[]>;

// A hook beállításai
interface UseCalendarOptions<T> {
  token: string;
  fetchData: FetchDataFunction<T>;
  initialDate?: Date;
  additionalParams?: Record<string, unknown>;
  keyExtractor?: (item: T) => Date | string | number;
}

export function useCalendar<T>({
  token,
  fetchData,
  initialDate = new Date(),
  additionalParams = {},
  keyExtractor,
}: UseCalendarOptions<T>) {
  // State for calendar view
  const [selectedDate, setSelectedDate] = useState(initialDate);
  const [selectedView, setSelectedView] = useState<"month" | "day">("month");
  const [selectedDay, setSelectedDay] = useState<Date | null>(null);
  const [exportDialogOpen, setExportDialogOpen] = useState(false);

  // Calculate date range for the current month
  const firstDayOfMonth = new Date(
    selectedDate.getFullYear(),
    selectedDate.getMonth(),
    1
  );
  const lastDayOfMonth = new Date(
    selectedDate.getFullYear(),
    selectedDate.getMonth() + 1,
    0
  );

  // Format for API request
  const startDate = format(firstDayOfMonth, "yyyy-MM-dd");
  const endDate = format(lastDayOfMonth, "yyyy-MM-dd");

  // Fetch data for the current month
  const { data, isLoading, isError, error } = useQuery({
    queryKey: [
      "calendarData",
      token,
      startDate,
      endDate,
      ...Object.values(additionalParams),
    ],
    queryFn: () =>
      fetchData({
        token,
        startDate,
        endDate,
        ...additionalParams,
      }),
  });

  function isTrip(item: unknown): item is Trip {
    return (
      item !== null &&
      typeof item === "object" &&
      "start_time" in item &&
      typeof (item as Trip).start_time === "string"
    );
  }

  function isFuelExpense(item: unknown): item is FuelExpense {
    return (
      item !== null &&
      typeof item === "object" &&
      "expense_date" in item &&
      typeof (item as FuelExpense).expense_date === "string"
    );
  }

  // Group data by day
  const dataByDay = data
    ? data.reduce(
        (acc, item) => {
          // Extract date from the item
          let dateValue: Date | string | number;

          if (keyExtractor) {
            dateValue = keyExtractor(item);
          } else if (isTrip(item)) {
            dateValue = new Date(item.start_time);
          } else if (isFuelExpense(item)) {
            dateValue = new Date(item.expense_date);
          } else {
            console.warn("Unable to extract date from item", item);
            return acc;
          }

          const date =
            dateValue instanceof Date ? dateValue : new Date(dateValue);
          const day = date.getDate();

          if (!acc[day]) {
            acc[day] = [];
          }

          acc[day].push(item);
          return acc;
        },
        {} as { [key: number]: T[] }
      )
    : {};

  // Navigation functions
  const navigateToPreviousMonth = () => {
    setSelectedDate(
      new Date(selectedDate.getFullYear(), selectedDate.getMonth() - 1, 1)
    );
    setSelectedDay(null);
    setSelectedView("month");
  };

  const navigateToNextMonth = () => {
    setSelectedDate(
      new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 1)
    );
    setSelectedDay(null);
    setSelectedView("month");
  };

  const handleDayClick = (day: Date) => {
    setSelectedDay(day);
    setSelectedView("day");
  };

  const navigateBackToMonth = () => {
    setSelectedView("month");
    setSelectedDay(null);
  };

  const handleExportClick = () => {
    setExportDialogOpen(true);
  };

  // Return all necessary state and functions
  return {
    selectedDate,
    setSelectedDate,
    selectedView,
    setSelectedView,
    selectedDay,
    setSelectedDay,
    exportDialogOpen,
    setExportDialogOpen,
    dataByDay,
    data,
    isLoading,
    isError,
    error,
    navigateToPreviousMonth,
    navigateToNextMonth,
    handleDayClick,
    navigateBackToMonth,
    handleExportClick,
    startDate,
    endDate,
  };
}

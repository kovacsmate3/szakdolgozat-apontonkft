"use client";

import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { format } from "date-fns";
import { hu } from "date-fns/locale";
import { ChevronLeft, ChevronRight } from "lucide-react";

interface MonthYearNavigatorProps {
  selectedDate: Date;
  onDateChange: (date: Date) => void;
  onPreviousMonth: () => void;
  onNextMonth: () => void;
  className?: string;
}

export function MonthYearNavigator({
  selectedDate,
  onDateChange,
  onPreviousMonth,
  onNextMonth,
  className,
}: MonthYearNavigatorProps) {
  const selectedYear = selectedDate.getFullYear();
  const selectedMonth = selectedDate.getMonth();

  const currentYear = new Date().getFullYear();
  const yearOptions = Array.from(
    { length: currentYear - 2003 + 2 },
    (_, index) => 2003 + index
  );

  const monthOptions = Array.from({ length: 12 }, (_, i) => {
    const date = new Date(2000, i, 1);
    return {
      value: i,
      label: format(date, "MMMM", { locale: hu }),
    };
  });

  const handleYearChange = (year: string) => {
    const newDate = new Date(selectedDate);
    newDate.setFullYear(parseInt(year));
    onDateChange(newDate);
  };

  const handleMonthChange = (month: string) => {
    const newDate = new Date(selectedDate);
    newDate.setMonth(parseInt(month));
    onDateChange(newDate);
  };

  return (
    <div className={`flex justify-between items-center w-full ${className}`}>
      <Button
        variant="ghost"
        onClick={onPreviousMonth}
        className="text-sm lg:text-base"
      >
        <ChevronLeft className="mr-1 h-4 w-4" />{" "}
        <span className="hidden sm:block">Előző hónap</span>
      </Button>

      <div className="flex items-center space-x-2">
        <Select
          value={selectedYear.toString()}
          onValueChange={handleYearChange}
        >
          <SelectTrigger className="w-[100px]">
            <SelectValue placeholder="Év" />
          </SelectTrigger>
          <SelectContent>
            {yearOptions.map((year) => (
              <SelectItem key={year} value={year.toString()}>
                {year}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>

        <Select
          value={selectedMonth.toString()}
          onValueChange={handleMonthChange}
        >
          <SelectTrigger className="w-[140px]">
            <SelectValue placeholder="Hónap" />
          </SelectTrigger>
          <SelectContent>
            {monthOptions.map((month) => (
              <SelectItem key={month.value} value={month.value.toString()}>
                {month.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      <Button
        variant="ghost"
        onClick={onNextMonth}
        className="text-sm lg:text-base"
      >
        <span className="hidden sm:block">Következő hónap</span>
        <ChevronRight className="ml-1 h-4 w-4" />
      </Button>
    </div>
  );
}

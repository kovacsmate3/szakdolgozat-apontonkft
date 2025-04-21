"use client";

import * as React from "react";
import { format, getMonth, getYear, setMonth, setYear } from "date-fns";
import { hu } from "date-fns/locale";
import { Calendar as CalendarIcon } from "lucide-react";

import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover-no-portal";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "./select";

interface DatePickerProps {
  value: Date | undefined;
  onChange: (date: Date | undefined) => void;
  startYear?: number;
  endYear?: number;
  disabled?: boolean;
}
export function DatePicker({
  value,
  onChange,
  startYear = getYear(new Date()) - 100,
  endYear = getYear(new Date()) + 100,
  disabled = false,
}: DatePickerProps) {
  const date = value ?? new Date();

  const months = [
    "január",
    "február",
    "március",
    "április",
    "május",
    "június",
    "július",
    "augusztus",
    "szeptember",
    "október",
    "november",
    "december",
  ];

  const years = Array.from(
    { length: endYear - startYear + 1 },
    (_, i) => startYear + i
  );

  const handleMonthChange = (month: string) => {
    onChange(setMonth(date, months.indexOf(month)));
  };

  const handleYearChange = (year: string) => {
    onChange(setYear(date, parseInt(year)));
  };

  const handleSelect = (selectedDate: Date | undefined) => {
    onChange(selectedDate);
  };

  return (
    <Popover>
      <PopoverTrigger asChild>
        <Button
          variant={"outline"}
          disabled={disabled}
          className={cn(
            "w-[250px] justify-start text-left font-normal",
            !date && "text-muted-foreground"
          )}
        >
          <CalendarIcon className="mr-2 h-4 w-4" />
          {date ? (
            format(date, "yyyy. MMMM d.", { locale: hu })
          ) : (
            <span>Válassz dátumot</span>
          )}
        </Button>
      </PopoverTrigger>
      {!disabled && (
        <PopoverContent className="w-auto p-0">
          <div className="flex justify-between p-2 gap-1">
            <Select
              onValueChange={handleYearChange}
              value={getYear(date).toString()}
            >
              <SelectTrigger className="min-w-[80px] w-[40%]">
                <SelectValue placeholder="Év" />
              </SelectTrigger>
              <SelectContent>
                {years.map((year) => (
                  <SelectItem key={year} value={year.toString()}>
                    {year}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <Select
              onValueChange={handleMonthChange}
              value={months[getMonth(date)]}
            >
              <SelectTrigger className="min-w-[140px] w-[60%]">
                <SelectValue placeholder="Hónap" />
              </SelectTrigger>
              <SelectContent>
                {months.map((month) => (
                  <SelectItem key={month} value={month}>
                    {month}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <Calendar
            mode="single"
            selected={date}
            onSelect={handleSelect}
            initialFocus
            month={date}
            onMonthChange={onChange}
            locale={hu}
          />
        </PopoverContent>
      )}
    </Popover>
  );
}

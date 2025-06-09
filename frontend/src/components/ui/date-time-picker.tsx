"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { format } from "date-fns";
import { useForm } from "react-hook-form";
import { z } from "zod";

import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Calendar } from "@/components/ui/calendar";
import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover-no-portal";
import { ScrollArea, ScrollBar } from "@/components/ui/scroll-area";
import { CalendarIcon } from "lucide-react";

// Add these props to make the component reusable
interface DateTimePickerProps {
  date: Date;
  setDate: (date: Date) => void;
  showFormElements?: boolean;
}

export function DateTimePicker({
  date,
  setDate,
  showFormElements = false,
}: DateTimePickerProps) {
  const FormSchema = z.object({
    time: z.date({
      required_error: "Dátum és idő megadása kötelező.",
    }),
  });

  const form = useForm<z.infer<typeof FormSchema>>({
    resolver: zodResolver(FormSchema),
    defaultValues: {
      time: date,
    },
  });

  function handleDateSelect(selectedDate: Date | undefined) {
    if (selectedDate) {
      // Update our form value
      form.setValue("time", selectedDate);

      // Also update the parent component's state
      // Preserve the time from the current date
      const newDate = new Date(selectedDate);
      newDate.setHours(date.getHours());
      newDate.setMinutes(date.getMinutes());
      setDate(newDate);
    }
  }

  function handleTimeChange(type: "hour" | "minute", value: string) {
    const currentDate = new Date(date);

    if (type === "hour") {
      const hour = parseInt(value, 10);
      currentDate.setHours(hour);
    } else if (type === "minute") {
      currentDate.setMinutes(parseInt(value, 10));
    }

    form.setValue("time", currentDate);
    setDate(currentDate);
  }

  const handleTimeClick = (
    e: React.MouseEvent,
    type: "hour" | "minute",
    value: string
  ) => {
    // Állítsd meg az esemény buborékozását
    e.preventDefault();
    // Időváltoztatás kezelése...
    handleTimeChange(type, value);
  };

  // If we don't want to show the full form (when used as a subcomponent)
  if (!showFormElements) {
    return (
      <Popover>
        <PopoverTrigger asChild>
          <Button
            variant={"outline"}
            className={cn(
              "w-full pl-3 text-left font-normal",
              !date && "text-muted-foreground"
            )}
          >
            {date ? (
              format(date, "yyyy.MM.dd HH:mm")
            ) : (
              <span>ÉÉÉÉ.HH.NN ÓÓ:PP</span>
            )}
            <CalendarIcon className="ml-auto h-4 w-4 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent
          className="w-auto p-0"
          align="start"
          side="bottom"
          alignOffset={0}
          sideOffset={5}
          avoidCollisions={false}
        >
          <div className="sm:flex">
            <Calendar
              mode="single"
              selected={date}
              onSelect={handleDateSelect}
              initialFocus
            />
            <div className="flex flex-col sm:flex-row sm:h-[300px] divide-y sm:divide-y-0 sm:divide-x">
              <ScrollArea className="w-64 sm:w-auto">
                <div className="flex sm:flex-col p-2">
                  {Array.from({ length: 24 }, (_, i) => i)
                    .reverse()
                    .map((hour) => (
                      <Button
                        key={hour}
                        size="icon"
                        variant={
                          date && date.getHours() === hour ? "default" : "ghost"
                        }
                        className="sm:w-full shrink-0 aspect-square"
                        onClick={(e) =>
                          handleTimeClick(e, "hour", hour.toString())
                        }
                      >
                        {hour}
                      </Button>
                    ))}
                </div>
                <ScrollBar orientation="horizontal" className="sm:hidden" />
              </ScrollArea>
              <ScrollArea className="w-64 sm:w-auto">
                <div className="flex sm:flex-col p-2">
                  {Array.from({ length: 60 }, (_, i) => i).map((minute) => (
                    <Button
                      key={minute}
                      size="icon"
                      variant={
                        date && date.getMinutes() === minute
                          ? "default"
                          : "ghost"
                      }
                      className="sm:w-full shrink-0 aspect-square"
                      onClick={(e) =>
                        handleTimeClick(e, "minute", minute.toString())
                      }
                    >
                      {minute.toString().padStart(2, "0")}
                    </Button>
                  ))}
                </div>
                <ScrollBar orientation="horizontal" className="sm:hidden" />
              </ScrollArea>
            </div>
          </div>
        </PopoverContent>
      </Popover>
    );
  }

  // Show the full form (for standalone use)
  return (
    <Form {...form}>
      <form className="space-y-5">
        <FormField
          control={form.control}
          name="time"
          render={({ field }) => (
            <FormItem className="flex flex-col">
              <FormLabel>Adja meg a dátumot és időpontot (24 órás)</FormLabel>
              <Popover>
                <PopoverTrigger asChild>
                  <FormControl>
                    <Button
                      variant={"outline"}
                      className={cn(
                        "w-full pl-3 text-left font-normal",
                        !field.value && "text-muted-foreground"
                      )}
                    >
                      {field.value ? (
                        format(field.value, "yyyy.MM.dd HH:mm")
                      ) : (
                        <span>ÉÉÉÉ.HH.NN ÓÓ:PP</span>
                      )}
                      <CalendarIcon className="ml-auto h-4 w-4 opacity-50" />
                    </Button>
                  </FormControl>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0">
                  <div className="sm:flex">
                    <Calendar
                      mode="single"
                      selected={field.value}
                      onSelect={handleDateSelect}
                      initialFocus
                    />
                    <div className="flex flex-col sm:flex-row sm:h-[300px] divide-y sm:divide-y-0 sm:divide-x">
                      <ScrollArea className="w-64 sm:w-auto">
                        <div className="flex sm:flex-col p-2">
                          {Array.from({ length: 24 }, (_, i) => i)
                            .reverse()
                            .map((hour) => (
                              <Button
                                key={hour}
                                size="icon"
                                variant={
                                  field.value && field.value.getHours() === hour
                                    ? "default"
                                    : "ghost"
                                }
                                className="sm:w-full shrink-0 aspect-square"
                                onClick={(e) =>
                                  handleTimeClick(e, "hour", hour.toString())
                                }
                              >
                                {hour}
                              </Button>
                            ))}
                        </div>
                        <ScrollBar
                          orientation="horizontal"
                          className="sm:hidden"
                        />
                      </ScrollArea>
                      <ScrollArea className="w-64 sm:w-auto">
                        <div className="flex sm:flex-col p-2">
                          {Array.from({ length: 60 }, (_, i) => i).map(
                            (minute) => (
                              <Button
                                key={minute}
                                size="icon"
                                variant={
                                  field.value &&
                                  field.value.getMinutes() === minute
                                    ? "default"
                                    : "ghost"
                                }
                                className="sm:w-full shrink-0 aspect-square"
                                onClick={(e) =>
                                  handleTimeClick(
                                    e,
                                    "minute",
                                    minute.toString()
                                  )
                                }
                              >
                                {minute.toString().padStart(2, "0")}
                              </Button>
                            )
                          )}
                        </div>
                        <ScrollBar
                          orientation="horizontal"
                          className="sm:hidden"
                        />
                      </ScrollArea>
                    </div>
                  </div>
                </PopoverContent>
              </Popover>
              <FormDescription>
                Kérem, válassza ki a kívánt dátumot és időpontot.
              </FormDescription>
              <FormMessage />
            </FormItem>
          )}
        />
      </form>
    </Form>
  );
}

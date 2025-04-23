"use client";

import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";
import { format } from "date-fns";

import {
  Dialog,
  DialogContent,
  DialogTrigger,
  DialogHeader,
  DialogTitle,
  DialogFooter,
  DialogDescription,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
} from "@/components/ui/form";
import { DatePicker } from "@/components/ui/date-picker";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { createFuelPrice, updateFuelPrice } from "@/server/fuel-prices";
import { FuelPrice } from "@/lib/types";
import { FuelPriceApiError } from "@/lib/errors";
import { formatPeriodToHungarianMonth } from "@/lib/functions";
import { fuelPriceFormSchema } from "@/lib/schemas";
import { Loader2 } from "lucide-react";

type FormValues = z.infer<typeof fuelPriceFormSchema>;

interface FuelPriceFormProps {
  token: string;
  initialData?: FuelPrice | null;
  isOpen?: boolean;
  onOpenChange?: (open: boolean) => void;
}

export function FuelPriceForm({
  token,
  initialData = null,
  isOpen: controlledIsOpen,
  onOpenChange: controlledOnOpenChange,
}: FuelPriceFormProps) {
  // Eldöntjük, hogy kontrollált vagy nem kontrollált módban működünk
  const isControlled =
    controlledIsOpen !== undefined && controlledOnOpenChange !== undefined;
  const [uncontrolledIsOpen, setUncontrolledIsOpen] = useState(false);

  // Használjuk a megfelelő állapotot
  const isOpen = isControlled ? controlledIsOpen : uncontrolledIsOpen;
  const setIsOpen = isControlled
    ? controlledOnOpenChange
    : setUncontrolledIsOpen;

  const form = useForm<FormValues>({
    resolver: zodResolver(fuelPriceFormSchema),
    defaultValues: {
      period: format(new Date(), "yyyy-MM-dd"),
      petrol: 0,
      mixture: 0,
      diesel: 0,
      lp_gas: 0,
    },
  });

  const queryClient = useQueryClient();

  // Form adatok betöltése szerkesztés esetén
  useEffect(() => {
    if (isOpen && initialData) {
      // Dátumok formázása
      const periodDate = new Date(initialData.period);

      form.reset({
        period: format(periodDate, "yyyy-MM-dd"),
        petrol: initialData.petrol,
        mixture: initialData.mixture,
        diesel: initialData.diesel,
        lp_gas: initialData.lp_gas,
      });
    } else if (isOpen && !initialData) {
      // Új létrehozás esetén alapértékek beállítása
      form.reset({
        period: format(new Date(), "yyyy-MM-dd"),
        petrol: 0,
        mixture: 0,
        diesel: 0,
        lp_gas: 0,
      });
    }
  }, [isOpen, initialData, form]);

  const createFuelPriceMutation = useMutation({
    mutationKey: ["create-fuelPrice", token],
    mutationFn: createFuelPrice,
    onSuccess: (data: { fuelPrice: FuelPrice }) => {
      queryClient.invalidateQueries({ queryKey: ["fuel-prices"] });
      const fuelPriceData = data.fuelPrice;
      console.log(fuelPriceData);
      const period = fuelPriceData?.period;
      if (period) {
        toast.success(
          `Üzemanyagár sikeresen létrehozva (${formatPeriodToHungarianMonth(period)})`,
          {
            duration: 4000,
          }
        );
      } else {
        toast.success(`Üzemanyagár sikeresen létrehozva`, {
          duration: 4000,
        });
      }
      setIsOpen(false);
      form.reset();
    },
    onError: (error: unknown, variables, context) => {
      console.log("Elkapott hiba:", error);
      console.log("variables:", variables);
      console.log("context:", context);
      if (error instanceof FuelPriceApiError && error.data?.errors) {
        Object.entries(error.data.errors).forEach(([field, messages]) => {
          form.setError(field as keyof FormValues, {
            type: "server",
            message: (messages as string[])[0],
          });
        });
      } else {
        toast.error("Ismeretlen hiba történt.");
      }
    },
  });

  // Frissítés mutáció
  const updateFuelPriceMutation = useMutation({
    mutationKey: ["update-fuelPrice", token],
    mutationFn: updateFuelPrice,
    onSuccess: (data: { fuelPrice: FuelPrice; message: string }) => {
      queryClient.invalidateQueries({ queryKey: ["fuel-prices"] });
      const period = data.fuelPrice?.period;
      if (period) {
        toast.success(
          `Üzemanyagár sikeresen frissítve (${formatPeriodToHungarianMonth(period)})`,
          {
            duration: 4000,
          }
        );
      } else {
        toast.success(`Üzemanyagár sikeresen frissítve`, {
          duration: 4000,
        });
      }
      setIsOpen(false);
      form.reset();
    },
    onError: (error: unknown) => {
      console.error("Frissítés hiba:", error);
      if (error instanceof FuelPriceApiError && error.data?.errors) {
        Object.entries(error.data.errors).forEach(([field, messages]) => {
          form.setError(field as keyof FormValues, {
            type: "server",
            message: (messages as string[])[0],
          });
        });
      } else {
        toast.error("Ismeretlen hiba történt a frissítés során.");
      }
    },
  });

  const onSubmit = async (values: FormValues) => {
    const periodDate = new Date(values.period);
    const firstDayOfMonth = new Date(
      periodDate.getFullYear(),
      periodDate.getMonth()
    );
    const formattedPeriod = format(firstDayOfMonth, "yyyy-MM-dd");

    const fuelPriceData = {
      ...values,
      period: formattedPeriod,
    };

    if (initialData) {
      // Frissítés
      updateFuelPriceMutation.mutate({
        id: initialData.id,
        fuelPrice: fuelPriceData,
        token: token || "",
      });
    } else {
      // Létrehozás
      createFuelPriceMutation.mutate({
        fuelPrice: fuelPriceData,
        token: token || "",
      });
    }
  };

  // Meghatározzuk a loading állapotot
  const isPending =
    createFuelPriceMutation.isPending || updateFuelPriceMutation.isPending;

  // UI elemek címei
  const dialogTitle = initialData
    ? "Üzemanyagár szerkesztése"
    : "Új üzemanyagár hozzáadása";

  const dialogDescription = initialData
    ? "Módosítsd a kiválasztott időszak üzemanyagárait."
    : "Add meg a NAV által közzétett üzemanyagárakat az adott időszakra.";

  const buttonText = initialData ? "Mentés" : "Új üzemanyagár";

  const dialogContent = (
    <DialogContent className="w-full max-w-3xl max-h-[95vh] overflow-y-auto p-12">
      <DialogHeader>
        <DialogTitle>{dialogTitle}</DialogTitle>
        <DialogDescription>{dialogDescription}</DialogDescription>
      </DialogHeader>

      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
          <FormField
            control={form.control}
            name="period"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Időszak</FormLabel>
                <FormControl>
                  <DatePicker
                    value={field.value ? new Date(field.value) : undefined}
                    onChange={(date) =>
                      field.onChange(date ? format(date, "yyyy-MM-dd") : "")
                    }
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="grid grid-cols-2 gap-4">
            <FormField
              control={form.control}
              name="petrol"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Benzin ára (Ft)</FormLabel>
                  <FormControl>
                    <Input type="number" step="0.01" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="mixture"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Keverék ára (Ft)</FormLabel>
                  <FormControl>
                    <Input type="number" step="0.01" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="diesel"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Dízel ára (Ft)</FormLabel>
                  <FormControl>
                    <Input type="number" step="0.01" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="lp_gas"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>LPG autógáz ára (Ft)</FormLabel>
                  <FormControl>
                    <Input type="number" step="0.01" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <DialogFooter>
            {initialData && (
              <Button
                type="button"
                variant="outline"
                onClick={() => setIsOpen(false)}
                className="mr-2"
              >
                Mégsem
              </Button>
            )}
            <Button
              type="submit"
              disabled={isPending || !form.formState.isValid}
            >
              {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              {buttonText}
            </Button>
          </DialogFooter>
        </form>
      </Form>
    </DialogContent>
  );

  if (isControlled) {
    return (
      <Dialog open={isOpen} onOpenChange={setIsOpen}>
        {dialogContent}
      </Dialog>
    );
  } else {
    // Nem kontrollált mód (létrehozáshoz, gombbal)
    return (
      <Dialog open={isOpen} onOpenChange={setIsOpen}>
        <DialogTrigger asChild>
          <Button>+ Új üzemanyagár</Button>
        </DialogTrigger>
        {dialogContent}
      </Dialog>
    );
  }
}

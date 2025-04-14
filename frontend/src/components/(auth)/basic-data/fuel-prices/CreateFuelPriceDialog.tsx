"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useSession } from "next-auth/react";
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

const formSchema = z.object({
  period: z.string().nonempty("Az időszak megadása kötelező."),
  petrol: z.coerce.number().min(0, "A benzin ára nem lehet negatív."),
  mixture: z.coerce.number().min(0, "A keverék ára nem lehet negatív."),
  diesel: z.coerce.number().min(0, "A dízel ára nem lehet negatív."),
  lp_gas: z.coerce.number().min(0, "Az LPG ára nem lehet negatív."),
});

type FormValues = z.infer<typeof formSchema>;

interface CreateFuelPriceDialogProps {
  onFuelPriceCreated?: () => void;
}

export function CreateFuelPriceDialog({
  onFuelPriceCreated,
}: CreateFuelPriceDialogProps) {
  const { data: session } = useSession();
  const token = session?.access_token;
  const [open, setOpen] = useState(false);

  const form = useForm<FormValues>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      period: format(new Date(), "yyyy-MM-dd"),
      petrol: 0,
      mixture: 0,
      diesel: 0,
      lp_gas: 0,
    },
  });

  const onSubmit = async (values: FormValues) => {
    try {
      const periodDate = new Date(values.period);
      const firstDayOfMonth = new Date(
        periodDate.getFullYear(),
        periodDate.getMonth(),
        1
      );
      const formattedPeriod = format(firstDayOfMonth, "yyyy-MM-dd");

      const res = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/fuel-prices`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({ ...values, period: formattedPeriod }),
        }
      );

      const data = await res.json();

      if (!res.ok) {
        if (res.status === 422 && data.errors) {
          Object.entries(data.errors).forEach(([field, messages]) => {
            form.setError(field as keyof FormValues, {
              type: "server",
              message: (messages as string[])[0],
            });
          });
        } else {
          toast.error(data.message || "Hiba történt.");
        }
        return;
      }

      onFuelPriceCreated?.();
      setOpen(false);
      form.reset();

      const displayPeriod = firstDayOfMonth.toLocaleDateString("hu-HU", {
        year: "numeric",
        month: "long",
      });
      toast.success(`Üzemanyagár sikeresen létrehozva (${displayPeriod})`, {
        duration: 4000,
      });
    } catch {
      toast.error("Hálózati hiba vagy váratlan probléma történt.");
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button>+ Új üzemanyagár</Button>
      </DialogTrigger>
      <DialogContent className="w-full max-w-3xl max-h-[95vh] overflow-y-auto p-12">
        <DialogHeader>
          <DialogTitle>Új üzemanyagár hozzáadása</DialogTitle>
          <DialogDescription>
            Add meg a NAV által közzétett üzemanyagárakat az adott időszakra.
          </DialogDescription>
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
              <Button type="submit">Mentés</Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}

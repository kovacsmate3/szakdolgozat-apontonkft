"use client";

import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";

import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
  DialogDescription,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Form,
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormMessage,
  FormDescription,
} from "@/components/ui/form";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import {
  createTravelPurpose,
  updateTravelPurpose,
} from "@/server/travel-purposes";
import { TravelPurposeDictionary } from "@/lib/types";
import { Loader2 } from "lucide-react";
import { travelPurposeDictionaryFormSchema } from "@/lib/schemas";
import { TravelPurposeDictionaryApiError } from "@/lib/errors";
import { Switch } from "@/components/ui/switch";

type FormValues = z.infer<typeof travelPurposeDictionaryFormSchema>;

interface TravelPurposeFormProps {
  token: string;
  travelPurposeToEdit?: TravelPurposeDictionary | null;
  isOpen?: boolean;
  onOpenChange?: (open: boolean) => void;
  isAdmin: boolean;
  userId: number; // Add current user's ID
}

export function TravelPurposeForm({
  token,
  travelPurposeToEdit = null,
  isOpen: controlledIsOpen,
  onOpenChange: controlledOnOpenChange,
  isAdmin,
  userId,
}: TravelPurposeFormProps) {
  const isControlled =
    controlledIsOpen !== undefined && controlledOnOpenChange !== undefined;
  const [uncontrolledIsOpen, setUncontrolledIsOpen] = useState(false);

  const isOpen = isControlled ? controlledIsOpen : uncontrolledIsOpen;
  const setIsOpen = isControlled
    ? controlledOnOpenChange
    : setUncontrolledIsOpen;

  const form = useForm<FormValues>({
    resolver: zodResolver(travelPurposeDictionaryFormSchema),
    mode: "onChange",
    defaultValues: {
      travel_purpose: "",
      type: isAdmin ? "Üzleti" : "Magán", // Different default type based on user role
      note: null,
      is_system: false, // Alapértelmezetten nem rendszerszintű
    },
  });

  const queryClient = useQueryClient();

  useEffect(() => {
    if (isOpen && travelPurposeToEdit) {
      // When editing an existing record
      form.reset({
        travel_purpose: travelPurposeToEdit.travel_purpose,
        type: travelPurposeToEdit.type,
        note: travelPurposeToEdit.note || null,
      });
    } else if (isOpen && !travelPurposeToEdit) {
      // When creating a new record
      form.reset({
        travel_purpose: "",
        type: isAdmin ? "Üzleti" : "Magán", // Different default based on role
        note: null,
      });
    }
  }, [isOpen, travelPurposeToEdit, form, isAdmin]);

  const createMutation = useMutation({
    mutationFn: createTravelPurpose,
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ["travel-purposes"] });
      toast.success("Utazási cél sikeresen létrehozva", {
        duration: 4000,
        description: data.travelPurpose.travel_purpose,
      });
      setIsOpen(false);
    },
    onError: (error: unknown) => {
      console.error("Létrehozás hiba részletes infó:", error);

      if (error instanceof TravelPurposeDictionaryApiError && error.data) {
        console.error("API hiba adatok:", error.data);

        if (error.data.errors) {
          Object.entries(error.data.errors).forEach(([field, messages]) => {
            form.setError(field as keyof FormValues, {
              type: "server",
              message: (messages as string[])[0],
            });
          });
        } else if (error.data.message) {
          toast.error(`Szerver hiba: ${error.data.message}`);
        } else {
          toast.error(`API hiba: ${error.status} státuszkód`);
        }
      } else if (error instanceof Error) {
        toast.error(`Hiba: ${error.message}`);
      } else {
        toast.error("Ismeretlen hiba történt a létrehozás során.");
      }
    },
  });

  const updateMutation = useMutation({
    mutationFn: updateTravelPurpose,
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ["travel-purposes"] });
      toast.success("Utazási cél sikeresen módosítva", {
        duration: 4000,
        description: data.travelPurpose.travel_purpose,
      });
      setIsOpen(false);
    },
    onError: (error: unknown) => {
      console.error("Módosítás hiba:", error);
      if (
        error instanceof TravelPurposeDictionaryApiError &&
        error.data?.errors
      ) {
        Object.entries(error.data.errors).forEach(([field, messages]) => {
          form.setError(field as keyof FormValues, {
            type: "server",
            message: (messages as string[])[0],
          });
        });
      } else {
        toast.error("Ismeretlen hiba történt a módosítás során.");
      }
    },
  });

  const onSubmit = (values: FormValues) => {
    const formattedValues = {
      ...values,
      note: values.note || null,
    };

    if (travelPurposeToEdit) {
      updateMutation.mutate({
        id: travelPurposeToEdit.id,
        travelPurpose: {
          ...formattedValues,
          is_system: isAdmin ? values.is_system : travelPurposeToEdit.is_system,
        },
        token,
      });
    } else {
      createMutation.mutate({
        travelPurpose: {
          ...formattedValues,
          is_system: isAdmin ? (values.is_system ?? false) : false,
          user_id: userId,
        },
        token,
      });
    }
  };

  const dialogTitle = travelPurposeToEdit
    ? "Utazási cél szerkesztése"
    : "Új utazási cél létrehozása";
  const dialogDescription = travelPurposeToEdit
    ? "Módosítsd a kiválasztott utazási cél adatait"
    : "Add meg az új utazási cél részleteit";

  const isPending = travelPurposeToEdit
    ? updateMutation.isPending
    : createMutation.isPending;

  const travelPurposeTypes = ["Magán", "Üzleti", "Egyéb"];

  // Rendszerszintű rekord ellenőrzése
  const isSystemRecord = travelPurposeToEdit?.is_system || false;
  // Saját rekord ellenőrzése
  const isOwnRecord = travelPurposeToEdit?.user_id === userId;
  // Módosítható-e a rekord?
  // Új rekord létrehozásakor (travelPurposeToEdit null) mindig engedélyezzük a szerkesztést
  const canEdit =
    !travelPurposeToEdit || // Új rekord létrehozása mindig engedélyezett
    (isAdmin &&
      // Admin által létrehozott rendszerszintű rekord
      ((isSystemRecord && travelPurposeToEdit.user_id === userId) ||
        // Nem rendszerszintű rekord, ami az adminhoz tartozik
        !isSystemRecord)) ||
    // Nem admin felhasználó saját, nem rendszerszintű rekordja
    (!isSystemRecord && isOwnRecord);

  const dialogContent = (
    <DialogContent className="w-full max-w-3xl max-h-[95vh] overflow-y-auto p-12">
      <DialogHeader>
        <DialogTitle>{dialogTitle}</DialogTitle>
        <DialogDescription>{dialogDescription}</DialogDescription>
      </DialogHeader>

      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
          {/* Utazási cél neve */}
          <FormField
            control={form.control}
            name="travel_purpose"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Utazási cél neve</FormLabel>
                <FormControl>
                  <Input
                    placeholder="pl. Értekezlet, Ügyféltalálkozó"
                    {...field}
                    disabled={!canEdit}
                  />
                </FormControl>
                <FormMessage />
                {!canEdit && travelPurposeToEdit && (
                  <FormDescription className="text-yellow-500">
                    Ez{" "}
                    {isSystemRecord
                      ? "rendszerszintű"
                      : "más felhasználó által létrehozott"}{" "}
                    utazási cél, nem módosítható.
                  </FormDescription>
                )}
              </FormItem>
            )}
          />
          {/* Típus kiválasztása */}
          <FormField
            control={form.control}
            name="type"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Típus</FormLabel>
                <Select
                  onValueChange={field.onChange}
                  value={field.value}
                  disabled={!canEdit}
                >
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Válassz típust..." />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    <SelectGroup>
                      {travelPurposeTypes.map((type) => (
                        <SelectItem key={type} value={type}>
                          {type}
                        </SelectItem>
                      ))}
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <FormMessage />
                {isSystemRecord && !isAdmin && (
                  <FormDescription className="text-yellow-500">
                    Rendszerszintű utazási célok típusa nem módosítható.
                  </FormDescription>
                )}
              </FormItem>
            )}
          />
          {isAdmin && (
            <FormField
              control={form.control}
              name="is_system"
              render={({ field }) => (
                <FormItem className="flex flex-row items-center justify-between rounded-lg border p-3 shadow-sm">
                  <div className="space-y-0.5">
                    <FormLabel>Rendszerszintű utazási cél</FormLabel>
                    <FormDescription>
                      A rendszerszintű utazási célok minden felhasználó számára
                      láthatók.
                    </FormDescription>
                  </div>
                  <FormControl>
                    <Switch
                      checked={field.value}
                      onCheckedChange={field.onChange}
                      disabled={!canEdit}
                    />
                  </FormControl>
                </FormItem>
              )}
            />
          )}
          {/* Megjegyzés (opcionális) */}
          <FormField
            control={form.control}
            name="note"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Megjegyzés (opcionális)</FormLabel>
                <FormControl>
                  <Input
                    placeholder="További részletek"
                    {...field}
                    value={field.value || ""}
                    disabled={!canEdit}
                  />
                </FormControl>
                <FormMessage />
                {isSystemRecord && !isAdmin && (
                  <FormDescription className="text-yellow-500">
                    Rendszerszintű utazási célok megjegyzése nem módosítható.
                  </FormDescription>
                )}
              </FormItem>
            )}
          />
          <DialogFooter className="gap-2 pt-4">
            {travelPurposeToEdit && (
              <Button
                type="button"
                variant="outline"
                onClick={() => setIsOpen(false)}
              >
                Mégsem
              </Button>
            )}
            <Button
              type="submit"
              disabled={isPending || !form.formState.isValid || !canEdit}
            >
              {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              {travelPurposeToEdit ? "Mentés" : "Létrehozás"}
            </Button>
          </DialogFooter>
        </form>
      </Form>
    </DialogContent>
  );

  // Return the dialog with the content
  return (
    <Dialog open={isOpen} onOpenChange={setIsOpen}>
      {dialogContent}
    </Dialog>
  );
}

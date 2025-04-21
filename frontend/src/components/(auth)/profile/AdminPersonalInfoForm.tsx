"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { format, parse } from "date-fns";
import { hu } from "date-fns/locale";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { DatePicker } from "@/components/ui/date-picker";
import { UserData } from "@/lib/types";
import { updateUser } from "@/server/users";
import { personalInfoSchema } from "@/lib/schemas";

interface AdminPersonalInfoFormProps {
  user: UserData;
  token: string;
  onUpdateSuccess?: (updatedUser: UserData) => void;
}

export function AdminPersonalInfoForm({
  user,
  token,
  onUpdateSuccess,
}: AdminPersonalInfoFormProps) {
  const [isEditing, setIsEditing] = useState(false);

  // Format birthdate for input and display
  const parsedBirthdate = new Date(user.birthdate);
  const displayFormattedBirthdate = format(parsedBirthdate, "yyyy. MMMM d.", {
    locale: hu,
  });

  const form = useForm<z.infer<typeof personalInfoSchema>>({
    resolver: zodResolver(personalInfoSchema),
    defaultValues: {
      lastname: user.lastname,
      firstname: user.firstname,
      birthdate: format(parsedBirthdate, "yyyy-MM-dd"),
    },
  });

  const queryClient = useQueryClient();

  const mutation = useMutation({
    mutationFn: (data: z.infer<typeof personalInfoSchema>) =>
      updateUser({
        id: user.id,
        user: data,
        token,
      }).then((response) => response.user),
    onSuccess: (updatedUser) => {
      queryClient.setQueryData(["user", user.id], updatedUser);
      toast.success("Személyes adatok sikeresen frissítve");
      setIsEditing(false);
      onUpdateSuccess?.(updatedUser);
    },
    onError: (error: unknown) => {
      // Check if error is a UserApiError with specific validation errors
      if (error instanceof Error && "data" in error) {
        const apiError = error as {
          data?: { errors?: Record<string, string[]> };
        };
        if (apiError.data?.errors) {
          // Handle server-side validation errors
          Object.entries(apiError.data.errors).forEach(([field, messages]) => {
            form.setError(field as keyof typeof personalInfoSchema.shape, {
              type: "server",
              message: messages[0],
            });
          });
          toast.error("Hibás adatok. Kérjük, ellenőrizze a bevitt adatokat.");
        } else {
          toast.error("Hiba történt az adatok frissítése során");
        }
      } else {
        toast.error("Hiba történt az adatok frissítése során");
      }
      console.error(error);
    },
  });

  function onSubmit(values: z.infer<typeof personalInfoSchema>) {
    mutation.mutate(values);
  }

  return (
    <div className="space-y-6">
      {!isEditing ? (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 className="text-sm font-medium mb-2">Vezetéknév</h3>
              <p className="text-foreground p-2 border rounded-md bg-muted/50">
                {user.lastname}
              </p>
            </div>

            <div>
              <h3 className="text-sm font-medium mb-2">Keresztnév</h3>
              <p className="text-foreground p-2 border rounded-md bg-muted/50">
                {user.firstname}
              </p>
            </div>
          </div>

          <div>
            <h3 className="text-sm font-medium mb-2">Születési dátum</h3>
            <p className="text-foreground p-2 border rounded-md bg-muted/50">
              {displayFormattedBirthdate}
            </p>
          </div>

          <Button
            type="button"
            onClick={() => setIsEditing(true)}
            className="w-full"
          >
            Adatok szerkesztése
          </Button>
        </>
      ) : (
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField
                control={form.control}
                name="lastname"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Vezetéknév</FormLabel>
                    <FormControl>
                      <Input {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="firstname"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Keresztnév</FormLabel>
                    <FormControl>
                      <Input {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <FormField
              control={form.control}
              name="birthdate"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Születési dátum</FormLabel>
                  <FormControl>
                    <DatePicker
                      value={
                        field.value
                          ? parse(field.value, "yyyy-MM-dd", new Date())
                          : undefined
                      }
                      onChange={(date) =>
                        field.onChange(date ? format(date, "yyyy-MM-dd") : "")
                      }
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="flex space-x-2">
              <Button
                type="submit"
                disabled={mutation.isPending}
                className="flex-1"
              >
                {mutation.isPending ? "Mentés folyamatban..." : "Mentés"}
              </Button>
              <Button
                type="button"
                variant="secondary"
                onClick={() => {
                  setIsEditing(false);
                  form.reset();
                }}
                className="flex-1"
              >
                Mégsem
              </Button>
            </div>
          </form>
        </Form>
      )}
    </div>
  );
}

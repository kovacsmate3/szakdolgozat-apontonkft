"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useMutation } from "@tanstack/react-query";
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
import { UserData } from "@/lib/types";
import { updateUser } from "@/server/users";

// Hungarian phone number validation regex
const hungarianPhoneRegex = /^(\+36|06)(20|30|70)\d{7}$/;

// Zod schema for contact info validation
const contactInfoSchema = z.object({
  email: z
    .string({ required_error: "Az email cím megadása kötelező." })
    .trim()
    .min(1, "Az email cím megadása kötelező.")
    .max(255, "Az email cím maximum 255 karakter hosszú lehet.")
    .email("Érvénytelen email cím formátum."),

  phonenumber: z
    .string({ required_error: "A telefonszám megadása kötelező." })
    .trim()
    .min(1, "A telefonszám megadása kötelező.")
    .max(30, "A telefonszám maximum 30 karakter hosszú lehet.")
    .regex(
      hungarianPhoneRegex,
      "Érvénytelen magyar telefonszám formátum. Használd a +36 vagy 06 előtagot, majd 20, 30 vagy 70 számot."
    ),
});

interface AdminContactInfoFormProps {
  user: UserData;
  token: string;
  onUpdateSuccess?: (updatedUser: UserData) => void;
}

export function AdminContactInfoForm({
  user,
  token,
  onUpdateSuccess,
}: AdminContactInfoFormProps) {
  const [isEditing, setIsEditing] = useState(false);

  const form = useForm<z.infer<typeof contactInfoSchema>>({
    resolver: zodResolver(contactInfoSchema),
    defaultValues: {
      email: user.email,
      phonenumber: user.phonenumber,
    },
  });

  const mutation = useMutation({
    mutationFn: (data: z.infer<typeof contactInfoSchema>) =>
      updateUser({
        id: user.id,
        user: data,
        token,
      }).then((response) => response.user),
    onSuccess: (updatedUser) => {
      toast.success("Kapcsolati adatok sikeresen frissítve");
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
            form.setError(field as keyof typeof contactInfoSchema.shape, {
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

  function onSubmit(values: z.infer<typeof contactInfoSchema>) {
    mutation.mutate(values);
  }

  return (
    <div className="space-y-6">
      {!isEditing ? (
        <>
          <div>
            <h3 className="text-sm font-medium mb-2">E-mail cím</h3>
            <p className="text-foreground p-2 border rounded-md bg-muted/50">
              {user.email}
            </p>
          </div>

          <div>
            <h3 className="text-sm font-medium mb-2">Telefonszám</h3>
            <p className="text-foreground p-2 border rounded-md bg-muted/50">
              {user.phonenumber}
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
            <FormField
              control={form.control}
              name="email"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>E-mail cím</FormLabel>
                  <FormControl>
                    <Input {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="phonenumber"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Telefonszám</FormLabel>
                  <FormControl>
                    <Input {...field} />
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

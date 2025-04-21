"use client";

import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Button } from "@/components/ui/button";
import { useMutation } from "@tanstack/react-query";
import { toast } from "sonner";
import { changePassword } from "@/server/users";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { PasswordChangeApiError } from "@/lib/errors";
import { PasswordChangeData } from "@/lib/types";

interface PasswordChangeFormProps {
  userId: number;
  token: string;
}

// Először definiáljuk az alap objektum sémát refine nélkül
const baseSchema = z.object({
  current_password: z.string().min(1, "A jelenlegi jelszó megadása kötelező."),
  password: z.string().min(1, "Az új jelszó megadása kötelező."),
  password_confirmation: z
    .string()
    .min(1, "Az új jelszó megerősítése kötelező."),
});

// Majd hozzáadjuk a refine szabályokat
const formSchema = baseSchema
  .refine((data) => data.password === data.password_confirmation, {
    message: "Az új jelszó és a megerősítés nem egyezik meg.",
    path: ["password_confirmation"],
  })
  .refine((data) => data.current_password !== data.password, {
    message: "Az új jelszó nem lehet azonos a jelenlegi jelszóval.",
    path: ["password"],
  });

type FormValues = z.infer<typeof formSchema>;

export function PasswordChangeForm({ userId, token }: PasswordChangeFormProps) {
  const form = useForm<FormValues>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      current_password: "",
      password: "",
      password_confirmation: "",
    },
  });

  const mutation = useMutation({
    mutationFn: (values: FormValues) => {
      return changePassword({
        userId,
        data: values as PasswordChangeData,
        token,
      });
    },
    onSuccess: () => {
      toast.success("A jelszó sikeresen módosítva lett.");
      form.reset();
    },
    onError: (error: unknown) => {
      if (error instanceof PasswordChangeApiError) {
        if (error.status === 422 && error.data.errors) {
          // Szerver oldali validációs hibák kezelése
          Object.entries(error.data.errors).forEach(([field, messages]) => {
            // Most már a baseSchema.shape létezik, mert ez egy egyszerű ZodObject
            if (field in baseSchema.shape) {
              form.setError(field as keyof FormValues, {
                type: "server",
                message: (messages as string[])[0],
              });
            }
          });
        } else if (error.status === 401) {
          form.setError("current_password", {
            type: "server",
            message: "A jelenlegi jelszó nem megfelelő.",
          });
        } else {
          toast.error(
            error.data.message || "Hiba történt a jelszó módosítása során."
          );
        }
      } else if (error instanceof Error) {
        toast.error(error.message);
      } else {
        toast.error("Hiba történt a jelszó módosítása során.");
      }
    },
  });

  function onSubmit(values: FormValues) {
    mutation.mutate(values);
  }

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
        <FormField
          control={form.control}
          name="current_password"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Jelenlegi jelszó</FormLabel>
              <FormControl>
                <Input type="password" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={form.control}
          name="password"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Új jelszó</FormLabel>
              <FormControl>
                <Input type="password" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={form.control}
          name="password_confirmation"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Új jelszó megerősítése</FormLabel>
              <FormControl>
                <Input type="password" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <Button type="submit" disabled={mutation.isPending} className="w-full">
          {mutation.isPending
            ? "Módosítás folyamatban..."
            : "Jelszó módosítása"}
        </Button>
      </form>
    </Form>
  );
}

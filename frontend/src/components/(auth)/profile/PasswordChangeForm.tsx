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
import { useState } from "react";
import { Eye, EyeOff } from "lucide-react";
import {
  passwordChangeBaseSchema,
  passwordChangeFormSchema,
} from "@/lib/schemas";

interface PasswordChangeFormProps {
  userId: number;
  token: string;
}

type FormValues = z.infer<typeof passwordChangeFormSchema>;

export function PasswordChangeForm({ userId, token }: PasswordChangeFormProps) {
  const [showPasswordFields, setShowPasswordFields] = useState({
    current: false,
    new: false,
    confirm: false,
  });

  const togglePasswordVisibility = (field: keyof typeof showPasswordFields) => {
    setShowPasswordFields((prev) => ({ ...prev, [field]: !prev[field] }));
  };

  const form = useForm<FormValues>({
    resolver: zodResolver(passwordChangeFormSchema),
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
            if (field in passwordChangeBaseSchema.shape) {
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
                <div className="relative">
                  <Input
                    type={showPasswordFields.current ? "text" : "password"}
                    {...field}
                    className="pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => togglePasswordVisibility("current")}
                    className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground"
                    tabIndex={-1}
                  >
                    {showPasswordFields.current ? (
                      <EyeOff className="w-5 h-5" />
                    ) : (
                      <Eye className="w-5 h-5" />
                    )}
                    <span className="sr-only">
                      Jelszó megjelenítése/elrejtése
                    </span>
                  </button>
                </div>
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
                <div className="relative">
                  <Input
                    type={showPasswordFields.new ? "text" : "password"}
                    {...field}
                    className="pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => togglePasswordVisibility("new")}
                    className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground"
                    tabIndex={-1}
                  >
                    {showPasswordFields.new ? (
                      <EyeOff className="w-5 h-5" />
                    ) : (
                      <Eye className="w-5 h-5" />
                    )}
                    <span className="sr-only">
                      Jelszó megjelenítése/elrejtése
                    </span>
                  </button>
                </div>
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
                <div className="relative">
                  <Input
                    type={showPasswordFields.confirm ? "text" : "password"}
                    {...field}
                    className="pr-10"
                  />
                  <button
                    type="button"
                    onClick={() => togglePasswordVisibility("confirm")}
                    className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground"
                    tabIndex={-1}
                  >
                    {showPasswordFields.confirm ? (
                      <EyeOff className="w-5 h-5" />
                    ) : (
                      <Eye className="w-5 h-5" />
                    )}
                    <span className="sr-only">
                      Jelszó megjelenítése/elrejtése
                    </span>
                  </button>
                </div>
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

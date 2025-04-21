"use client";

import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
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
import { UserData } from "@/lib/types";
import { updateUser } from "@/server/users";
import { contactInfoSchema } from "@/lib/schemas";

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
  // Helyi állapot a megjelenített adatokhoz
  const [displayedUser, setDisplayedUser] = useState(user);

  const form = useForm<z.infer<typeof contactInfoSchema>>({
    resolver: zodResolver(contactInfoSchema),
    defaultValues: {
      email: user.email,
      phonenumber: user.phonenumber,
    },
  });

  useEffect(() => {
    form.reset({
      email: user.email,
      phonenumber: user.phonenumber,
    });
    setDisplayedUser(user);
  }, [user, form]);

  const queryClient = useQueryClient();

  const mutation = useMutation({
    mutationFn: (data: z.infer<typeof contactInfoSchema>) =>
      updateUser({
        id: user.id,
        user: data,
        token,
      }).then((response) => response.user),
    onSuccess: (updatedUser) => {
      queryClient.setQueryData(["user", user.id], updatedUser);
      setDisplayedUser(updatedUser);
      queryClient.invalidateQueries({ queryKey: ["user", user.id] });
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
              {displayedUser.email}
            </p>
          </div>

          <div>
            <h3 className="text-sm font-medium mb-2">Telefonszám</h3>
            <p className="text-foreground p-2 border rounded-md bg-muted/50">
              {displayedUser.phonenumber}
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

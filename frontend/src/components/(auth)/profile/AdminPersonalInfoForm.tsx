"use client";

import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { format, parse } from "date-fns";
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
import { useSession } from "next-auth/react";

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
  const queryClient = useQueryClient();
  const { update: updateSession } = useSession();

  // Format birthdate for input and display
  const parsedBirthdate = new Date(user.birthdate);

  const form = useForm<z.infer<typeof personalInfoSchema>>({
    resolver: zodResolver(personalInfoSchema),
    defaultValues: {
      lastname: user.lastname,
      firstname: user.firstname,
      birthdate: format(parsedBirthdate, "yyyy-MM-dd"),
    },
  });

  useEffect(() => {
    form.reset({
      lastname: user.lastname,
      firstname: user.firstname,
      birthdate: format(new Date(user.birthdate), "yyyy-MM-dd"),
    });
  }, [user, form]);

  const mutation = useMutation({
    mutationFn: (data: z.infer<typeof personalInfoSchema>) =>
      updateUser({
        id: user.id,
        user: data,
        token,
      }).then((response) => response.user),
    onSuccess: (updatedUser) => {
      // Update all queries with this user ID
      queryClient.setQueryData(["user", user.id], updatedUser);
      queryClient.setQueryData(["user", user.id, token], updatedUser);

      // Invalidate and refetch relevant queries
      queryClient.invalidateQueries({ queryKey: ["user", user.id] });
      queryClient.invalidateQueries({ queryKey: ["user", user.id, token] });

      // Update the session with new user data
      updateSession({
        user: {
          ...user,
          firstname: updatedUser.firstname,
          lastname: updatedUser.lastname,
          name: `${updatedUser.firstname} ${updatedUser.lastname}`,
          birthdate: updatedUser.birthdate,
        },
      });
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

  // Külön függvény a form küldéséhez - csak akkor fut, ha explicit meghívjuk
  const handleFormSubmit = () => {
    form.handleSubmit((values) => {
      mutation.mutate(values);
    })();
  };

  // Ha szerkesztési módban vagyunk, akkor engedélyezzük a form küldését
  const handleOnSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (isEditing) {
      handleFormSubmit();
    }
  };

  return (
    <div className="space-y-6">
      <Form {...form}>
        <form onSubmit={handleOnSubmit} className="space-y-6">
          <FormField
            control={form.control}
            name="lastname"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Vezetéknév</FormLabel>
                <FormControl>
                  <Input disabled={!isEditing} {...field} />
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
                  <Input disabled={!isEditing} {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="birthdate"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Születési dátum</FormLabel>
                <FormControl>
                  <DatePicker
                    disabled={!isEditing}
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
        </form>
      </Form>

      <div className="flex space-x-2">
        {!isEditing ? (
          <Button
            type="button"
            className="flex-1"
            onClick={() => setIsEditing(true)}
          >
            Adatok szerkesztése
          </Button>
        ) : (
          <>
            <Button
              type="submit"
              className="flex-1"
              disabled={mutation.isPending}
              onClick={handleFormSubmit}
            >
              {mutation.isPending ? "Mentés folyamatban..." : "Mentés"}
            </Button>
            <Button
              type="button"
              variant="secondary"
              className="flex-1"
              onClick={() => {
                form.reset();
                setIsEditing(false);
              }}
            >
              Mégsem
            </Button>
          </>
        )}
      </div>
    </div>
  );
}

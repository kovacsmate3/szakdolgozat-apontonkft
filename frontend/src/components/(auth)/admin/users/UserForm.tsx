"use client";

import { useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";

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
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
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
} from "@/components/ui/form";
import { DatePicker } from "@/components/ui/date-picker";
import { parse, format } from "date-fns";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { createUser } from "@/server/users";
import { UserData } from "@/lib/types";
import { UserApiError } from "@/lib/errors";
import { userFormSchema } from "@/lib/schemas";

type FormValues = z.infer<typeof userFormSchema>;

interface UserFormProps {
  token: string;
}

export function UserForm({ token }: UserFormProps) {
  const [open, setOpen] = useState(false);

  const form = useForm<FormValues>({
    resolver: zodResolver(userFormSchema),
    defaultValues: {
      username: "",
      firstname: "",
      lastname: "",
      birthdate: "",
      phonenumber: "",
      email: "",
      password: "",
      role_id: undefined,
    },
  });

  const queryClient = useQueryClient();

  const createUserMutation = useMutation({
    mutationKey: ["create-user", token],
    mutationFn: createUser,
    onSuccess: (data: { user: UserData }) => {
      queryClient.invalidateQueries({ queryKey: ["users"] });
      toast.success("Felhasználó sikeresen létrehozva", {
        duration: 4000,
        description: (
          <div className="space-y-1">
            <p>Felhasználónév: {data.user.username}</p>
            <p>Szerepkör: {data.user.role?.title ?? "Ismeretlen"}</p>
          </div>
        ),
      });
      setOpen(false);
      form.reset();
    },
    onError: (error: unknown, variables, context) => {
      console.log("Elkapott hiba:", error);
      console.log("variables:", variables);
      console.log("context:", context);
      if (error instanceof UserApiError && error.data?.errors) {
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

  const onSubmit = (values: FormValues) => {
    createUserMutation.mutate({
      user: { ...values, role_id: parseInt(values.role_id) },
      token: token || "",
    });
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button>+ Új felhasználó</Button>
      </DialogTrigger>
      <DialogContent className="w-full max-w-3xl max-h-[95vh] overflow-y-auto p-12">
        <DialogHeader>
          <DialogTitle>Új felhasználó létrehozása</DialogTitle>
          <DialogDescription>Töltsd ki az alábbi adatokat</DialogDescription>
        </DialogHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            {[
              { name: "lastname", label: "Vezetéknév" },
              { name: "firstname", label: "Keresztnév" },
              { name: "username", label: "Felhasználónév" },
            ].map(({ name, label }) => (
              <FormField
                key={name}
                control={form.control}
                name={name as keyof FormValues}
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>{label}</FormLabel>
                    <FormControl>
                      <Input {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            ))}

            {/* Születési dátum */}
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
                          ? parse(field.value, "yyyy-MM-dd", new Date()) // ez a fontos
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

            {[
              { name: "email", label: "Email", type: "email" },
              { name: "phonenumber", label: "Telefonszám" },
              { name: "password", label: "Jelszó", type: "password" },
            ].map(({ name, label, type }) => (
              <FormField
                key={name}
                control={form.control}
                name={name as keyof FormValues}
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>{label}</FormLabel>
                    <FormControl>
                      <Input type={type || "text"} {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            ))}

            <FormField
              control={form.control}
              name="role_id"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Szerepkör</FormLabel>
                  <Select onValueChange={field.onChange} value={field.value}>
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Válassz szerepkört..." />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectGroup>
                        <SelectLabel>Szerepkörök</SelectLabel>
                        <SelectItem value="1">Webfejlesztő</SelectItem>
                        <SelectItem value="2">Admin</SelectItem>
                        <SelectItem value="3">Alkalmazott</SelectItem>
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            <DialogFooter>
              <Button type="submit" disabled={createUserMutation.isPending}>
                {createUserMutation.isPending ? "Létrehozás..." : "Létrehozás"}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}

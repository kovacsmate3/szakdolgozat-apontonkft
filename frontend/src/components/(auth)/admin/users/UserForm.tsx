"use client";

import { useEffect, useState } from "react";
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
import { format } from "date-fns";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { createUser, updateUser } from "@/server/users";
import { UserData } from "@/lib/types";
import { UserApiError } from "@/lib/errors";
import { userCreateSchema, userEditSchema } from "@/lib/schemas";
import { Loader2 } from "lucide-react";

type FormValues = z.infer<typeof userCreateSchema>;

interface UserFormProps {
  token: string;
  userToEdit?: UserData | null;
  isOpen?: boolean;
  onOpenChange?: (open: boolean) => void;
}

export function UserForm({
  token,
  userToEdit = null,
  isOpen: controlledIsOpen,
  onOpenChange: controlledOnOpenChange,
}: UserFormProps) {
  // Eldöntjük, hogy kontrollált vagy nem kontrollált módban működünk
  const isControlled =
    controlledIsOpen !== undefined && controlledOnOpenChange !== undefined;
  const [uncontrolledIsOpen, setUncontrolledIsOpen] = useState(false);

  // Használjuk a megfelelő állapotot
  const isOpen = isControlled ? controlledIsOpen : uncontrolledIsOpen;
  const setIsOpen = isControlled
    ? controlledOnOpenChange
    : setUncontrolledIsOpen;

  // A megfelelő séma kiválasztása a mód alapján
  const schema = userToEdit ? userEditSchema : userCreateSchema;

  const form = useForm<FormValues>({
    resolver: zodResolver(schema),
    mode: "onChange",
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

  // Form resetelése modal nyitásakor/zárásakor
  useEffect(() => {
    if (isOpen) {
      // Ha szerkesztés, akkor betöltjük az adatokat
      if (userToEdit) {
        form.reset({
          username: userToEdit.username,
          firstname: userToEdit.firstname,
          lastname: userToEdit.lastname,
          birthdate: userToEdit.birthdate,
          phonenumber: userToEdit.phonenumber,
          email: userToEdit.email,
          role_id: userToEdit.role_id.toString() as "1" | "2" | "3",
          password: "", // Üres jelszó szerkesztéskor
        });
      } else {
        // Létrehozásnál tiszta form
        form.reset({
          username: "",
          firstname: "",
          lastname: "",
          birthdate: "",
          phonenumber: "",
          email: "",
          password: "",
          role_id: undefined,
        });
      }
    }
  }, [isOpen, userToEdit, form]);

  const createMutation = useMutation({
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
      setIsOpen(false);
    },
    onError: (error: unknown) => {
      console.error("Létrehozás hiba:", error);
      if (error instanceof UserApiError && error.data?.errors) {
        Object.entries(error.data.errors).forEach(([field, messages]) => {
          form.setError(field as keyof FormValues, {
            type: "server",
            message: (messages as string[])[0],
          });
        });
      } else {
        toast.error("Ismeretlen hiba történt a létrehozás során.");
      }
    },
  });

  // Frissítés mutation
  const updateMutation = useMutation({
    mutationFn: updateUser,
    onSuccess: (data: { user: UserData }) => {
      queryClient.invalidateQueries({ queryKey: ["users"] });
      toast.success("Felhasználó sikeresen módosítva", {
        duration: 4000,
        description: `${data.user.lastname} ${data.user.firstname} (${data.user.username}) adatai frissítve.`,
      });
      setIsOpen(false);
    },
    onError: (error: unknown) => {
      console.error("Szerkesztés hiba:", error);
      if (error instanceof UserApiError && error.data?.errors) {
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
    if (userToEdit) {
      // Eltávolítjuk a password-t, ha üres
      const { password, role_id, ...restValues } = values;

      // Csak akkor adjuk hozzá a password-t, ha nem üres
      const updateData = {
        ...restValues,
        role_id: parseInt(role_id),
        ...(password ? { password } : {}),
      };

      // Frissítés
      updateMutation.mutate({
        id: userToEdit.id,
        user: updateData,
        token,
      });
    } else {
      // Létrehozási eset
      const createData = {
        ...values,
        role_id: parseInt(values.role_id),
      };

      // Létrehozás
      createMutation.mutate({
        user: createData,
        token,
      });
    }
  };

  // Modal állapot változásakor
  const handleOpenChange = (open: boolean) => {
    setIsOpen(open);
  };

  // UI elemek szövegei a mód alapján
  const dialogTitle = userToEdit
    ? "Felhasználó szerkesztése"
    : "Új felhasználó létrehozása";
  const dialogDescription = userToEdit
    ? "Módosítsd a felhasználó adatait"
    : "Töltsd ki az alábbi adatokat";
  const buttonText = userToEdit ? "Mentés" : "Létrehozás";
  const isPending = userToEdit
    ? updateMutation.isPending
    : createMutation.isPending;

  // Dialog tartalom
  const dialogContent = (
    <DialogContent className="w-full max-w-3xl max-h-[95vh] overflow-y-auto p-12">
      <DialogHeader>
        <DialogTitle>{dialogTitle}</DialogTitle>
        <DialogDescription>{dialogDescription}</DialogDescription>
      </DialogHeader>

      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
          {/* Alapadatok */}
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

          {/* Kapcsolati adatok */}
          <FormField
            control={form.control}
            name="email"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                  <Input type="email" {...field} />
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

          {/* Jelszó mező */}
          <FormField
            control={form.control}
            name="password"
            render={({ field }) => (
              <FormItem>
                <FormLabel>
                  {userToEdit ? "Jelszó (csak változtatáshoz)" : "Jelszó"}
                </FormLabel>
                <FormControl>
                  <Input type="password" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          {/* Szerepkör */}
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

          <DialogFooter className="gap-2 pt-4">
            {userToEdit && (
              <Button
                type="button"
                variant="outline"
                onClick={() => handleOpenChange(false)}
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

  // Visszatérési érték a mód alapján
  if (isControlled) {
    // Kontrollált mód (szerkesztéshez)
    return (
      <Dialog open={isOpen} onOpenChange={handleOpenChange}>
        {dialogContent}
      </Dialog>
    );
  } else {
    // Nem kontrollált mód (létrehozáshoz, gombbal)
    return (
      <Dialog open={isOpen} onOpenChange={handleOpenChange}>
        <DialogTrigger asChild>
          <Button>+ Új felhasználó</Button>
        </DialogTrigger>
        {dialogContent}
      </Dialog>
    );
  }
}

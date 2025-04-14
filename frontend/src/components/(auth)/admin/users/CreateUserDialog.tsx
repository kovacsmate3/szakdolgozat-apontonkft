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
import { format } from "date-fns";
import { useSession } from "next-auth/react";

const formSchema = z.object({
  username: z
    .string({ required_error: "A felhasználónév megadása kötelező." })
    .min(1, "A felhasználónév megadása kötelező.")
    .max(25, "A felhasználónév maximum 25 karakter hosszú lehet."),
  firstname: z
    .string({ required_error: "A keresztnév megadása kötelező." })
    .min(1, "A keresztnév megadása kötelező.")
    .max(50, "A keresztnév maximum 50 karakter hosszú lehet."),
  lastname: z
    .string({ required_error: "A vezetéknév megadása kötelező." })
    .min(1, "A vezetéknév megadása kötelező.")
    .max(50, "A vezetéknév maximum 50 karakter hosszú lehet."),
  birthdate: z
    .string({ required_error: "A születési dátum megadása kötelező." })
    .refine((date) => {
      const d = new Date(date);
      const min = new Date();
      min.setFullYear(min.getFullYear() - 18);
      return d <= min;
    }, "A felhasználónak legalább 18 évesnek kell lennie."),
  phonenumber: z
    .string()
    .min(1, "A telefonszám megadása kötelező.")
    .max(30, "A telefonszám maximum 30 karakter hosszú lehet.")
    .regex(
      /^(\+36|06)(20|30|70)\d{7}$/,
      "Érvénytelen magyar telefonszám formátum."
    ),
  email: z
    .string()
    .min(1, "Az email cím megadása kötelező.")
    .email("Érvénytelen email cím formátum.")
    .max(255, "Az email cím maximum 255 karakter hosszú lehet."),
  password: z
    .string()
    .min(1, "A jelszó megadása kötelező.")
    .superRefine((val, ctx) => {
      if (val.length < 8) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak legalább 8 karakter hosszúnak kell lennie.",
        });
      }
      if (!/[a-z]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell kisbetűt is.",
        });
      }
      if (!/[A-Z]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell nagybetűt is.",
        });
      }
      if (!/\d/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "A jelszónak tartalmaznia kell legalább egy számot.",
        });
      }
      if (!/[^a-zA-Z0-9]/.test(val)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message:
            "A jelszónak tartalmaznia kell legalább egy speciális karaktert.",
        });
      }
    }),

  role_id: z.enum(["1", "2", "3"], {
    required_error: "A szerepkör kiválasztása kötelező.",
  }),
});

type FormValues = z.infer<typeof formSchema>;

interface CreateUserDialogProps {
  onUserCreated?: () => void;
}

export function CreateUserDialog({ onUserCreated }: CreateUserDialogProps) {
  const { data: session } = useSession();
  const token = session?.access_token;
  const [open, setOpen] = useState(false);

  const form = useForm<FormValues>({
    resolver: zodResolver(formSchema),
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

  const onSubmit = async (values: FormValues) => {
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/users`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ ...values, role_id: parseInt(values.role_id) }),
      });

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

      onUserCreated?.();
      setOpen(false);
      form.reset();
      toast.success("Felhasználó létrehozva", {
        duration: 4000,
        description: `• Felhasználónév: ${data.user.username}
• Név: ${data.user.lastname} ${data.user.firstname}
• Email: ${data.user.email}
• Szerepkör: ${data.user.role?.title ?? "Ismeretlen"}`,
      });
    } catch {
      toast.error("Hálózati hiba vagy váratlan probléma.");
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button>+ Új felhasználó</Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
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
                  <Select
                    onValueChange={field.onChange}
                    defaultValue={field.value}
                  >
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Válassz szerepkört..." />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectGroup>
                        <SelectLabel>Szerepkörök</SelectLabel>
                        <SelectItem value="1">Admin</SelectItem>
                        <SelectItem value="2">Webfejlesztő</SelectItem>
                        <SelectItem value="3">Alkalmazott</SelectItem>
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            <DialogFooter>
              <Button type="submit">Létrehozás</Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
}

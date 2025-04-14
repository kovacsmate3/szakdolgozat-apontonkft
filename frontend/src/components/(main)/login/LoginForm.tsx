"use client";

import { signIn } from "next-auth/react";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { LoaderCircle } from "lucide-react";
import { useForm } from "react-hook-form";
import { useRouter } from "next/navigation";

interface LoginFormValues {
  identifier: string;
  password: string;
  remember: boolean;
}

export default function LoginForm() {
  const router = useRouter();
  const form = useForm<LoginFormValues>({
    defaultValues: {
      identifier: "",
      password: "",
      remember: false,
    },
  });

  const {
    handleSubmit,
    formState: { isSubmitting },
    setError,
  } = form;

  const onSubmit = async (values: LoginFormValues) => {
    if (!values.identifier) {
      setError("identifier", {
        type: "manual",
        message: "Az email cím vagy felhasználónév megadása kötelező.",
      });
    } else if (
      values.identifier.includes("@") &&
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.identifier)
    ) {
      setError("identifier", {
        type: "manual",
        message: "Érvénytelen email formátum.",
      });
    }

    if (!values.password) {
      setError("password", {
        type: "manual",
        message: "A jelszó megadása kötelező.",
      });
    }

    if (!values.identifier || !values.password) return;

    const res = await signIn("credentials", {
      ...values,
      redirect: false,
      callbackUrl: "/dashboard",
    });

    if (res && res.error) {
      setError("identifier", {
        type: "manual",
        message: "Hibás email/felhasználónév vagy jelszó.",
      });
      setError("password", {
        type: "manual",
        message: "Ellenőrizd a megadott adatokat.",
      });
    } else if (res && res.ok) {
      sessionStorage.setItem("justLoggedIn", "true");
      router.push("/dashboard");
    }
  };

  return (
    <Card className="w-full max-w-md mx-auto bg-white dark:bg-neutral-800 shadow-md">
      <CardHeader>
        <CardTitle>Lépj be a felhasználói fiókodba</CardTitle>
        <CardDescription>
          Add meg az email címedet vagy felhasználónevedet, illetve a
          jelszavadat a bejelentkezéshez
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Form {...form}>
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            <FormField
              control={form.control}
              name="identifier"
              render={({ field }) => (
                <FormItem>
                  <FormLabel htmlFor="identifier">
                    Email / Felhasználónév
                  </FormLabel>
                  <FormControl>
                    <Input
                      id="identifier"
                      type="text"
                      placeholder="email@example.com / username"
                      autoComplete="username"
                      {...field}
                    />
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
                  <FormLabel htmlFor="password">Jelszó</FormLabel>
                  <FormControl>
                    <Input
                      id="password"
                      type="password"
                      placeholder="Jelszó"
                      autoComplete="current-password"
                      {...field}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="remember"
              render={({ field }) => (
                <FormItem className="flex items-center space-x-2">
                  <FormControl>
                    <Checkbox
                      id="remember"
                      checked={field.value}
                      onCheckedChange={field.onChange}
                    />
                  </FormControl>
                  <FormLabel htmlFor="remember">Emlékezz rám</FormLabel>
                  <FormMessage />
                </FormItem>
              )}
            />

            <Button type="submit" className="w-full" disabled={isSubmitting}>
              {isSubmitting && (
                <LoaderCircle className="h-4 w-4 animate-spin mr-2" />
              )}
              Bejelentkezés
            </Button>
          </form>
        </Form>
      </CardContent>
    </Card>
  );
}

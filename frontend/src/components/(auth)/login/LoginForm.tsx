"use client";

import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import InputError from "@/components/ui/input-error";
import { Button } from "@/components/ui/button";
import { LoaderCircle } from "lucide-react";
import { FormEvent, useState } from "react";
import { Checkbox } from "@/components/ui/checkbox";
import { useRouter } from "next/navigation";

const validateEmail = (email: string) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

type LoginFormData = {
  identifier: string;
  password: string;
  remember: boolean;
};

const LoginForm = () => {
  const [data, setData] = useState<LoginFormData>({
    identifier: "",
    password: "",
    remember: false,
  });

  const router = useRouter();

  const [processing, setProcessing] = useState(false);
  const [errors, setErrors] = useState<{ [key: string]: string }>({});

  const handleChange = (
    field: keyof LoginFormData,
    value: string | boolean
  ) => {
    setData((prev) => ({ ...prev, [field]: value }));
    setErrors((prev) => ({ ...prev, [field]: "" }));
  };

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setProcessing(true);
    setErrors({});

    if (!data.identifier) {
      setErrors((prev) => ({
        ...prev,
        identifier: "Az email cím vagy felhasználónév megadása kötelező.",
      }));
    }
    if (!data.password) {
      setErrors((prev) => ({
        ...prev,
        password: "A jelszó megadása kötelező.",
      }));
    }

    if (data.identifier.includes("@") && !validateEmail(data.identifier)) {
      setErrors((prev) => ({
        ...prev,
        identifier: "Érvénytelen email formátum.",
      }));
      setProcessing(false);
      return;
    }

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/login`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });

      const result = await res.json();

      if (!res.ok) {
        if (result.errors) {
          setErrors((prev) => ({
            ...prev,
            identifier: result.errors.identifier
              ? result.errors.identifier[0]
              : "",
            password: result.errors.password ? result.errors.password[0] : "",
          }));
        } else if (result.message) {
          setErrors((prev) => ({
            ...prev,
            general: result.message,
          }));
        }
      } else {
        localStorage.setItem("access_token", result.token);
        localStorage.setItem("user", JSON.stringify(result.user));
        router.push("/");
      }
    } catch (error) {
      console.error("Bejelentkezési hiba:", error);
      setErrors((prev) => ({
        ...prev,
        general: "Hiba történt a bejelentkezés során.",
      }));
    } finally {
      setProcessing(false);
    }
  };

  return (
    <form className="flex flex-col gap-6" onSubmit={submit}>
      <div className="grid gap-6">
        <div className="grid gap-2">
          <Label htmlFor="email">Email/Felhasználónév</Label>
          <Input
            id="identifier"
            type="text"
            tabIndex={1}
            value={data.identifier}
            onChange={(e) => handleChange("identifier", e.target.value)}
            placeholder="email@example.com/username"
          />
          <InputError message={errors.identifier} />
        </div>

        <div className="grid gap-2">
          <Input
            id="password"
            type="password"
            tabIndex={2}
            value={data.password}
            onChange={(e) => handleChange("password", e.target.value)}
            placeholder="Jelszó"
          />
          <InputError message={errors.password} />
        </div>

        <div className="flex items-center space-x-3">
          <Checkbox
            id="remember"
            name="remember"
            checked={data.remember}
            onClick={() => handleChange("remember", !data.remember)}
            tabIndex={3}
          />
          <Label htmlFor="remember">Emlékezz rám</Label>
        </div>
        <div className="grid gap-2">
          <InputError message={errors.general} />
        </div>
        <Button
          type="submit"
          className="mt-4 w-full"
          tabIndex={4}
          disabled={processing}
        >
          {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
          Bejelentkezés
        </Button>
      </div>
    </form>
  );
};

export default LoginForm;

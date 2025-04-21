"use client";

import { format } from "date-fns";
import { UserData } from "@/lib/types";

interface PersonalInfoFormProps {
  user: UserData;
  token: string; // Megtartjuk a kompatibilitás miatt, de nem használjuk
}

export function PersonalInfoForm({ user }: PersonalInfoFormProps) {
  // Formázott születési dátum
  const formattedBirthdate = format(new Date(user.birthdate), "yyyy. MMMM d.");

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 className="text-sm font-medium mb-2">Vezetéknév</h3>
          <p className="text-foreground p-2 border rounded-md bg-muted/50">
            {user.lastname}
          </p>
        </div>

        <div>
          <h3 className="text-sm font-medium mb-2">Keresztnév</h3>
          <p className="text-foreground p-2 border rounded-md bg-muted/50">
            {user.firstname}
          </p>
        </div>
      </div>

      <div>
        <h3 className="text-sm font-medium mb-2">Születési dátum</h3>
        <p className="text-foreground p-2 border rounded-md bg-muted/50">
          {formattedBirthdate}
        </p>
      </div>

      <div className="bg-amber-50 dark:bg-amber-950/30 p-4 rounded-md border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm">
        <p>A személyes adatok módosításához fordulj az adminisztrátorhoz.</p>
      </div>
    </div>
  );
}

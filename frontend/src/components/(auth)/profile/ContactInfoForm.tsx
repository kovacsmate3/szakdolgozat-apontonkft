"use client";

import { UserData } from "@/lib/types";

interface ContactInfoFormProps {
  user: UserData;
  token: string;
}

export function ContactInfoForm({ user }: ContactInfoFormProps) {
  return (
    <div className="space-y-6">
      <div>
        <h3 className="text-sm font-medium mb-2">E-mail cím</h3>
        <p className="text-foreground p-2 border rounded-md bg-muted/50">
          {user.email}
        </p>
      </div>

      <div>
        <h3 className="text-sm font-medium mb-2">Telefonszám</h3>
        <p className="text-foreground p-2 border rounded-md bg-muted/50">
          {user.phonenumber}
        </p>
      </div>

      <div className="bg-amber-50 dark:bg-amber-950/30 p-4 rounded-md border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm">
        <p>A kapcsolati adatok módosításához fordulj az adminisztrátorhoz.</p>
      </div>
    </div>
  );
}

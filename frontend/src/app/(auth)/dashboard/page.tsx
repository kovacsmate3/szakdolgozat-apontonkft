"use client";

import { DashboardHelpSection } from "@/components/(auth)/dashboard/DashboardHelpSection";
import { useSession } from "next-auth/react";
import { useEffect, useRef } from "react";
import { toast } from "sonner";

export default function DashboardHomePage() {
  const { data: session } = useSession();
  const toastShownRef = useRef(false);

  useEffect(() => {
    const isJustLoggedIn = sessionStorage.getItem("justLoggedIn") === "true";

    if (session?.user && isJustLoggedIn && !toastShownRef.current) {
      toast(`Üdv újra, ${session.user.name || "felhasználó"}!`, {
        description: `Sikeres bejelentkezés (${session.user.email})`,
      });

      sessionStorage.removeItem("justLoggedIn");
      toastShownRef.current = true;
    }
  }, [session]);

  return (
    <>
      <div className="grid auto-rows-min gap-4 md:grid-cols-3">
        <div className="bg-muted/50 aspect-video rounded-xl" />
        <div className="bg-muted/50 aspect-video rounded-xl" />
        <div className="bg-muted/50 aspect-video rounded-xl" />
      </div>
      <div className="bg-muted/50 min-h-[100vh] flex-1 rounded-xl md:min-h-min" />
      <DashboardHelpSection />
    </>
  );
}

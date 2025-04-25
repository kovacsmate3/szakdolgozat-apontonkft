"use client";

import { useSession } from "next-auth/react";
import { useEffect, useRef } from "react";
import { toast } from "sonner";
import CarMileageChart from "@/components/(auth)/dashboard/CarMileageChart";
import TravelPurposeChart from "@/components/(auth)/dashboard/TravelPurposeChart";
import FuelCostChart from "@/components/(auth)/dashboard/FuelCostChart";
import LocationStatsChart from "@/components/(auth)/dashboard/LocationStatsChart";
import { VscGraph } from "react-icons/vsc";

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
    <div className="container mx-auto py-10">
      {/* Módosított fejléc, hogy hasonlítson a többi oldalhoz */}
      <div className="flex items-center gap-3 mb-8">
        <VscGraph className="size-8 text-primary" />
        <h1 className="text-3xl font-bold">Irányítópult</h1>
      </div>

      {/* Leírás szekció */}
      <p className="text-gray-600 dark:text-gray-400 mb-6">
        Ezen az oldalon átfogó képet kap a járművek használatáról, az utazási
        célokról, az üzemanyag költségekről és a leggyakrabban látogatott
        helyszínekről. Az adatok a <strong>2024-es év</strong> alapján kerülnek
        megjelenítésre.
      </p>

      <div className="grid grid-cols-1 auto-rows-min gap-6 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
        <div className="min-w-0 md:col-span-1 lg:col-span-2 xl:col-span-3">
          <CarMileageChart />
        </div>

        <div className="md:col-span-1">
          <FuelCostChart />
        </div>

        <div className="md:col-span-1 my-auto">
          <TravelPurposeChart />
        </div>

        <div className="md:col-span-1">
          <LocationStatsChart />
        </div>
      </div>
    </div>
  );
}

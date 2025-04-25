"use client";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";
import { TrendingUp, TrendingDown } from "lucide-react";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  ChartConfig,
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
  ChartLegend,
  ChartLegendContent,
} from "@/components/ui/chart";
import { useSession } from "next-auth/react";
import { useQuery } from "@tanstack/react-query";
import { formatDistance } from "@/lib/functions";
import { Car, Trip } from "@/lib/types";
import { getTrips } from "@/server/trips";
import { getCars } from "@/server/cars";

// A hónapok nevei
const months = [
  "Január",
  "Február",
  "Március",
  "Április",
  "Május",
  "Június",
  "Július",
  "Augusztus",
  "Szeptember",
  "Október",
  "November",
  "December",
];

// Chart Data type
interface ChartDataItem {
  month: string;
  [key: string]: string | number;
}

export default function CarMileageChart() {
  const { data: session } = useSession();
  const token = session?.user?.access_token;
  const year = 2024;

  // Autók lekérdezése a server/cars.ts fájlból importált függvénnyel
  const { data: cars = [] } = useQuery<Car[]>({
    queryKey: ["cars", token],
    queryFn: async () => {
      if (!token) throw new Error("Hiányzó token");
      return getCars({ queryKey: ["cars", token] });
    },
    enabled: !!token,
  });

  const {
    data: trips = [],
    isLoading,
    error,
  } = useQuery<Trip[]>({
    queryKey: ["trips", token, year],
    queryFn: async () => {
      if (!token) throw new Error("Hiányzó token");
      const startDate = `${year}-01-01`;
      const endDate = `${year}-12-31`;
      return getTrips({
        token,
        startDate,
        endDate,
      });
    },
    enabled: !!token && cars.length > 0,
  });

  // Chart adatok számítása
  const chartData: ChartDataItem[] = months.map((month, index) => {
    const monthNumber = index + 1;
    const result: ChartDataItem = { month };

    // Autónként 0 kezdő értékek
    cars.forEach((car) => {
      result[`car_${car.id}`] = 0;
    });

    // A hónap tripjei
    const monthTrips = trips.filter((trip) => {
      const tripDate = new Date(trip.start_time);
      return tripDate.getMonth() + 1 === monthNumber;
    });

    // Autónként a megtett távolság számítása a hónapban
    cars.forEach((car) => {
      const carTrips = monthTrips.filter((trip) => trip.car_id === car.id);
      if (carTrips.length > 0) {
        const distance = carTrips.reduce((total: number, trip) => {
          // Távolság meghatározása: először az actual_distance-t használjuk, ha nincs, akkor kilométeróra alapján, végül a planned_distance
          const tripDistance =
            trip.actual_distance !== null
              ? trip.actual_distance
              : trip.end_odometer && trip.start_odometer
                ? trip.end_odometer - trip.start_odometer
                : trip.planned_distance || 0;

          return total + tripDistance;
        }, 0);

        result[`car_${car.id}`] = Math.round(distance);
      }
    });

    return result;
  });

  // Összesített adatok számítása
  const totalDistance = trips.reduce((total: number, trip) => {
    const distance =
      trip.actual_distance !== null
        ? trip.actual_distance
        : trip.end_odometer && trip.start_odometer
          ? trip.end_odometer - trip.start_odometer
          : trip.planned_distance || 0;

    return total + distance;
  }, 0);

  // Növekedés/csökkenés számítása az előző hónaphoz képest
  const currentMonth = new Date().getMonth();
  const prevMonth = currentMonth - 1 >= 0 ? currentMonth - 1 : 11;

  const currentMonthTotal = Object.entries(chartData[currentMonth] || {})
    .filter(([key]) => key.startsWith("car_"))
    .reduce((sum: number, [, value]) => sum + (value as number), 0);

  const prevMonthTotal = Object.entries(chartData[prevMonth] || {})
    .filter(([key]) => key.startsWith("car_"))
    .reduce((sum: number, [, value]) => sum + (value as number), 0);

  const percentChange =
    prevMonthTotal > 0
      ? ((currentMonthTotal - prevMonthTotal) / prevMonthTotal) * 100
      : 0;

  const comparisonPercent = Math.abs(percentChange).toFixed(1);
  const isIncreasing = percentChange >= 0;

  // Chart konfigurációk létrehozása
  const chartConfig: ChartConfig = {};
  cars.forEach((car, index) => {
    // Különböző színek az autókhoz
    const colors = [
      "hsl(var(--chart-1))",
      "hsl(var(--chart-2))",
      "hsl(var(--chart-3))",
      "hsl(var(--chart-4))",
      "hsl(var(--chart-5))",
    ];

    chartConfig[`car_${car.id}`] = {
      label: `${car.license_plate}`,
      color: colors[index % colors.length],
    };
  });

  // Ha betöltés alatt van
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Autók megtett kilométerei</CardTitle>
          <CardDescription>Adatok betöltése...</CardDescription>
        </CardHeader>
        <CardContent className="h-80 flex items-center justify-center">
          <div className="animate-pulse text-muted-foreground">
            Adatok betöltése...
          </div>
        </CardContent>
      </Card>
    );
  }

  // Ha hiba történt
  if (error) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Autók megtett kilométerei</CardTitle>
          <CardDescription className="text-red-500">
            Hiba történt az adatok betöltésekor
          </CardDescription>
        </CardHeader>
        <CardContent className="h-80 flex items-center justify-center">
          <div className="text-red-500">{(error as Error).message}</div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="w-full">
      <CardHeader>
        <CardTitle>Autók megtett kilométerei</CardTitle>
        <CardDescription>2024. évi eloszlás</CardDescription>
      </CardHeader>
      <CardContent>
        {chartData.length > 0 && cars.length > 0 ? (
          <ChartContainer
            config={chartConfig}
            className="w-full h-48 sm:h-56 md:h-64 lg:h-[300px] min-w-0"
          >
            <BarChart accessibilityLayer data={chartData}>
              <CartesianGrid vertical={false} strokeDasharray="3 3" />
              <XAxis
                dataKey="month"
                tickLine={false}
                tickMargin={10}
                axisLine={false}
                tickFormatter={(value) => value.slice(0, 3)}
              />
              <YAxis
                tickLine={false}
                tickMargin={10}
                axisLine={false}
                tickFormatter={(value) => `${value} km`}
              />
              <ChartTooltip content={<ChartTooltipContent />} />
              <ChartLegend content={<ChartLegendContent />} />
              {cars.map((car, index) => {
                const barColorsWithAccent = [
                  "#4f46e5",
                  "#f59e0b",
                  "#0ea5e9",
                  "#10b981",
                  "#2563eb",
                  "#10b981",
                  "#ef4444",
                  "#f59e0b",
                ];

                return (
                  <Bar
                    key={car.id}
                    dataKey={`car_${car.id}`}
                    fill={
                      barColorsWithAccent[index % barColorsWithAccent.length]
                    }
                    radius={4}
                  />
                );
              })}
            </BarChart>
          </ChartContainer>
        ) : (
          <div className="h-full flex items-center justify-center text-muted-foreground">
            Nincs elegendő adat a diagram megjelenítéséhez
          </div>
        )}
      </CardContent>
      <CardFooter className="flex-col items-start gap-2 text-sm">
        <div className="flex gap-2 font-medium leading-none">
          {isIncreasing ? (
            <>
              Növekedés: {comparisonPercent}% az előző hónaphoz képest{" "}
              <TrendingUp className="h-4 w-4 text-green-500" />
            </>
          ) : (
            <>
              Csökkenés: {comparisonPercent}% az előző hónaphoz képest{" "}
              <TrendingDown className="h-4 w-4 text-red-500" />
            </>
          )}
        </div>
        <div className="leading-none text-muted-foreground">
          Összes megtett távolság: {formatDistance(totalDistance)} a 2024-es
          évben
        </div>
      </CardFooter>
    </Card>
  );
}

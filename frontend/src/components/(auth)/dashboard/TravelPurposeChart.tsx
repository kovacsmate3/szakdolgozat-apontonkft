"use client";

import {
  PieChart,
  Pie,
  Cell,
  ResponsiveContainer,
  Legend,
  Tooltip,
} from "recharts";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useSession } from "next-auth/react";
import { useQuery } from "@tanstack/react-query";
import { Trip, TravelPurposeDictionary } from "@/lib/types";

// Chart adattípus
interface ChartDataItem {
  name: string;
  value: number;
}

// Recharts Tooltip props típusa
type TooltipProps = {
  active?: boolean;
  payload?: Array<{
    value: number;
    name: string;
    payload: ChartDataItem;
    dataKey: string;
    color: string;
  }>;
  label?: string;
};

// Recharts Legend props típusa
type LegendProps = {
  payload?: Array<{
    value: string;
    id?: string;
    type?: string;
    color: string;
  }>;
};

// Egyedi színpaletta az utazási célokhoz
const COLORS = [
  "#3b82f6", // kék
  "#22c55e", // zöld
  "#f59e0b", // sárga
  "#ef4444", // piros
  "#8b5cf6", // lila
  "#06b6d4", // világoskék
  "#ec4899", // rózsaszín
  "#a3e635", // citromzöld
  "#fb7185", // lazac
  "#f43f5e", // piros
];

export default function TravelPurposeChart() {
  const { data: session } = useSession();
  const token = session?.user?.access_token;
  const year = 2024;

  // Utazási célok lekérdezése React Query-vel
  const { data: travelPurposes = [] } = useQuery<TravelPurposeDictionary[]>({
    queryKey: ["travelPurposes", token],
    queryFn: async () => {
      const res = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/travel-purpose-dictionaries`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      if (!res.ok) throw new Error("Utazási célok betöltése sikertelen");
      return res.json();
    },
    enabled: !!token,
  });

  // Utak lekérdezése React Query-vel
  const {
    data: trips = [],
    isLoading,
    error,
  } = useQuery<Trip[]>({
    queryKey: ["trips", token, year],
    queryFn: async () => {
      const startDate = `${year}-01-01`;
      const endDate = `${year}-12-31`;
      const res = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/trips?start_date=${startDate}&end_date=${endDate}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      if (!res.ok) throw new Error("Utak betöltése sikertelen");
      return res.json();
    },
    enabled: !!token && travelPurposes.length > 0,
  });

  // Adatok előkészítése a kördiagramhoz
  const prepareChartData = (): ChartDataItem[] => {
    // Utazási célok szerint csoportosítjuk az utakat
    const purposeCounts: Record<string, number> = {};
    const purposeByIdMap: Record<number, string> = {};

    // Létrehozzuk a purpose ID és név leképezését
    travelPurposes.forEach((purpose) => {
      purposeByIdMap[purpose.id] = purpose.travel_purpose;
    });

    // Megszámoljuk az utazásokat céltípus szerint
    trips.forEach((trip) => {
      if (trip.dict_id) {
        const purposeName = purposeByIdMap[trip.dict_id] || "Ismeretlen";
        purposeCounts[purposeName] = (purposeCounts[purposeName] || 0) + 1;
      }
    });

    // Átalakítjuk a megfelelő formátumra a kördiagramhoz
    return Object.entries(purposeCounts)
      .map(([name, value]) => ({ name, value }))
      .sort((a, b) => b.value - a.value)
      .slice(0, 10); // Top 10 célkategóriát jelenítjük meg
  };

  const chartData = prepareChartData();
  const totalTrips = trips.length;

  // Ha betöltés alatt van
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Utazási célok megoszlása</CardTitle>
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
          <CardTitle>Utazási célok megoszlása</CardTitle>
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

  // Egyedi tooltip a kördiagramhoz
  const CustomTooltip = ({ active, payload }: TooltipProps) => {
    if (active && payload && payload.length) {
      const data = payload[0].payload;
      const percentage = ((data.value / totalTrips) * 100).toFixed(1);

      return (
        <div className="bg-background border rounded-md shadow-md p-2 text-sm">
          <p className="font-medium">{data.name}</p>
          <p>Utak száma: {data.value}</p>
          <p>Arány: {percentage}%</p>
        </div>
      );
    }
    return null;
  };

  // Egyedi jelmagyarázat
  const CustomLegend = ({ payload }: LegendProps) => {
    if (!payload) return null;

    return (
      <ul className="flex flex-wrap justify-center gap-4 mt-4">
        {payload.map((entry, index) => (
          <li key={`legend-${index}`} className="flex items-center gap-2">
            <div
              className="w-3 h-3 rounded-full"
              style={{ backgroundColor: entry.color }}
            />
            <span className="text-xs">{entry.value}</span>
          </li>
        ))}
      </ul>
    );
  };

  // Egyedi stílus a címkékhez
  const RADIAN = Math.PI / 180;
  const renderCustomizedLabel = ({
    cx,
    cy,
    midAngle,
    innerRadius,
    outerRadius,
    percent,
  }: {
    cx: number;
    cy: number;
    midAngle: number;
    innerRadius: number;
    outerRadius: number;
    percent: number;
    index: number;
  }) => {
    const radius = innerRadius + (outerRadius - innerRadius) * 0.5;
    const x = cx + radius * Math.cos(-midAngle * RADIAN);
    const y = cy + radius * Math.sin(-midAngle * RADIAN);

    return (
      <text
        x={x}
        y={y}
        fill="black"
        textAnchor="middle"
        dominantBaseline="central"
        style={{ fontSize: "14px", fontWeight: "bold" }}
      >
        {`${(percent * 100).toFixed(0)}%`}
      </text>
    );
  };

  return (
    <Card className="w-full">
      <CardHeader>
        <CardTitle>Utazási célok megoszlása</CardTitle>
        <CardDescription>2024. évi adatok alapján</CardDescription>
      </CardHeader>
      <CardContent className="h-80">
        {chartData.length > 0 ? (
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie
                data={chartData}
                cx="50%"
                cy="50%"
                labelLine={false}
                label={renderCustomizedLabel}
                outerRadius={90}
                innerRadius={20}
                paddingAngle={0}
                dataKey="value"
                stroke="none"
                strokeWidth={0}
              >
                {chartData.map((entry, index) => (
                  <Cell
                    key={`cell-${index}`}
                    fill={COLORS[index % COLORS.length]}
                  />
                ))}
              </Pie>
              <Tooltip content={<CustomTooltip />} />
              <Legend content={<CustomLegend />} />
            </PieChart>
          </ResponsiveContainer>
        ) : (
          <div className="h-full flex items-center justify-center text-muted-foreground">
            Nincs elegendő adat a diagram megjelenítéséhez
          </div>
        )}
      </CardContent>
    </Card>
  );
}

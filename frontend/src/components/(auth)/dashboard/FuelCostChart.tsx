"use client";

import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from "recharts";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useQuery } from "@tanstack/react-query";
import { formatHUF } from "@/lib/functions";
import { Ban } from "lucide-react";
import { ChartProps, FuelExpense, FuelPrice } from "@/lib/types";

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

// Adattípus definiálása a charthoz
interface ChartDataItem {
  month: string;
  totalCost: number;
  avgLiterPrice: number;
  fuelQuantity: number;
}

// Payload típus a tooltip-hez
interface TooltipPayload {
  value: number;
  name: string;
  dataKey: string;
  color: string;
  payload: ChartDataItem;
}

// Egyedi tooltip interfész pontos típusokkal
interface CustomTooltipProps {
  active?: boolean;
  payload?: Array<TooltipPayload>;
  label?: string;
}

export default function FuelCostChart({ token }: ChartProps) {
  const year = 2024;

  // Üzemanyagárak lekérdezése React Query-vel
  const { data: fuelPrices = [] } = useQuery<FuelPrice[]>({
    queryKey: ["fuelPrices", token],
    queryFn: async () => {
      const res = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/fuel-prices`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      if (!res.ok) throw new Error("Üzemanyagárak betöltése sikertelen");
      return res.json();
    },
    enabled: !!token,
  });

  // Tankolások lekérdezése React Query-vel
  const {
    data: fuelExpenses = [],
    isLoading,
    error,
  } = useQuery<FuelExpense[]>({
    queryKey: ["fuelExpenses", token, year],
    queryFn: async () => {
      const startDate = `${year}-01-01`;
      const endDate = `${year}-12-31`;
      const res = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/fuel-expenses?from_date=${startDate}&to_date=${endDate}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      if (!res.ok) throw new Error("Tankolási adatok betöltése sikertelen");
      return res.json();
    },
    enabled: !!token && fuelPrices.length > 0,
  });

  // Adatok előkészítése a vonaldiagramhoz
  const prepareChartData = (): ChartDataItem[] => {
    const monthlyCosts = months.map((month, index) => {
      const monthNumber = index + 1;

      // A hónap tankolásai
      const monthExpenses = fuelExpenses.filter((expense) => {
        const expenseDate = new Date(expense.expense_date);
        return expenseDate.getMonth() + 1 === monthNumber;
      });

      // Havi összegzés
      const totalAmount = monthExpenses.reduce(
        (sum: number, expense) => sum + expense.amount,
        0
      );
      const totalFuelQuantity = monthExpenses.reduce(
        (sum: number, expense) => sum + expense.fuel_quantity,
        0
      );

      const averagePrice =
        totalFuelQuantity > 0 ? totalAmount / totalFuelQuantity : 0;

      return {
        month,
        totalCost: Math.round(totalAmount),
        avgLiterPrice: Math.round(averagePrice),
        fuelQuantity: Math.round(totalFuelQuantity),
      };
    });

    return monthlyCosts;
  };

  const chartData = prepareChartData();

  // Összesített adatok számítása
  const totalExpense = fuelExpenses.reduce(
    (sum: number, expense) => sum + expense.amount,
    0
  );
  const totalFuelQuantity = fuelExpenses.reduce(
    (sum: number, expense) => sum + expense.fuel_quantity,
    0
  );
  const avgPricePerLiter =
    totalFuelQuantity > 0 ? totalExpense / totalFuelQuantity : 0;

  // Egyedi tooltip a részletes adatok megjelenítéséhez
  const CustomTooltip = ({ active, payload, label }: CustomTooltipProps) => {
    if (active && payload && payload.length) {
      const data = payload[0].payload;
      return (
        <div className="bg-card border border-border rounded-md shadow-md p-2 text-xs max-w-[180px]">
          <h3 className="font-medium text-sm mb-1">{data.month || label}</h3>
          <div className="grid grid-cols-2 gap-2">
            <div className="text-muted-foreground">Teljes költség:</div>
            <div className="font-medium text-right">
              {formatHUF(data.totalCost)}
            </div>

            <div className="text-muted-foreground">Átlagos literár:</div>
            <div className="font-medium text-right">
              {formatHUF(data.avgLiterPrice)}
            </div>

            <div className="text-muted-foreground">Tankolás mennyiség:</div>
            <div className="font-medium text-right">
              {data.fuelQuantity} liter
            </div>
          </div>
        </div>
      );
    }
    return null;
  };

  const modernColors = [
    "#1E88E5", // sapphire - kék
    "#00A676", // emerald - zöld
    "#FF5252", // piros
    "#FFC107", // amber - sárga/narancs
    "#7B1FA2", // lila
    "#607D8B", // kékesszürke
    "#795548", // barna
  ];

  // Ha betöltés alatt van
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Üzemanyag költségek</CardTitle>
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
          <CardTitle>Üzemanyag költségek</CardTitle>
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

  // Ha nincs adat
  if (fuelExpenses.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Üzemanyag költségek</CardTitle>
          <CardDescription>2024. évi adatok</CardDescription>
        </CardHeader>
        <CardContent className="h-80 flex flex-col items-center justify-center">
          <Ban className="h-12 w-12 text-muted-foreground mb-4" />
          <div className="text-muted-foreground">
            Nincs rögzített tankolási adat
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="w-full">
      <CardHeader>
        <CardTitle>Üzemanyag költségek</CardTitle>
        <CardDescription>2024. évi adatok</CardDescription>
      </CardHeader>
      <CardContent className="h-80">
        <ResponsiveContainer width="100%" height={325}>
          <LineChart
            data={chartData}
            margin={{ top: 10, right: 25, left: 35, bottom: 5 }}
          >
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis
              dataKey="month"
              tickLine={false}
              tickMargin={15}
              axisLine={false}
              interval={0} // Legfontosabb: ez biztosítja, hogy minden hónap megjelenjen
              angle={-55} // Megdöntött címkék a jobb olvashatóságért
              textAnchor="end" // A dőlt szöveg jobb igazítása
              height={55} // Több hely az X tengelynek
              tick={{ fontSize: 11 }} // Kisebb betűméret
              tickFormatter={(value: string) => value.slice(0, 3)}
            />
            <YAxis
              yAxisId="left"
              tickLine={false}
              tickMargin={10}
              axisLine={false}
              width={60}
              tickFormatter={(value: number) => `${value} Ft`}
            />
            <YAxis
              yAxisId="right"
              orientation="right"
              tickLine={false}
              tickMargin={10}
              axisLine={false}
              width={60}
              tickFormatter={(value: number) => `${value} Ft`}
            />
            {/* Használjuk a Recharts Tooltip-et */}
            <Tooltip
              content={<CustomTooltip />}
              position={{ y: 0 }}
              offset={10}
            />
            <Line
              yAxisId="left"
              type="monotone"
              dataKey="totalCost"
              stroke={modernColors[0]}
              strokeWidth={2.5}
              dot={{ r: 3, fill: modernColors[0] }}
              activeDot={{ r: 6, fill: modernColors[0] }}
            />
            <Line
              yAxisId="right"
              type="monotone"
              dataKey="avgLiterPrice"
              stroke={modernColors[2]}
              strokeWidth={2.5}
              dot={{ r: 3, fill: modernColors[2] }}
              activeDot={{ r: 6, fill: modernColors[2] }}
            />
          </LineChart>
        </ResponsiveContainer>
      </CardContent>
      <CardFooter className="flex-col items-center gap-2 text-sm pb-1">
        <div className="leading-none text-muted-foreground">
          Összes költség: {formatHUF(totalExpense)}
        </div>
        <div className="leading-none text-muted-foreground">
          Átlagos literár: {formatHUF(avgPricePerLiter)} / liter
        </div>
        <div className="leading-none text-muted-foreground">
          Összesen tankolt: {totalFuelQuantity.toFixed(2)} liter
        </div>
      </CardFooter>
    </Card>
  );
}

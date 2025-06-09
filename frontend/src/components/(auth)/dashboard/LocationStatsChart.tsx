"use client";

import { useEffect, useState } from "react";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Cell,
} from "recharts";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { useQuery } from "@tanstack/react-query";
import { formatDistance } from "@/lib/functions";
import { ChartProps, Location, Trip } from "@/lib/types";
import { getLocations } from "@/server/locations";
import { getTrips } from "@/server/trips";
import { Ban } from "lucide-react";

// Helyszín statisztika chart adattípus
interface LocationStatsItem {
  name: string;
  visits: number;
  distance: number;
}

// Tooltip payload típus
interface TooltipPayload {
  name: string;
  value: number;
  dataKey: string;
  color: string;
  payload: LocationStatsItem;
}

// Tooltip props interfész
interface CustomTooltipProps {
  active?: boolean;
  payload?: Array<TooltipPayload>;
  label?: string;
}

// Modern színpaletta (emerald és sapphire hangsúllyal)
const LOCATION_COLORS = [
  "#1E88E5", // sapphire kék
  "#00A676", // emerald zöld
  "#FF5252", // piros
  "#FFC107", // amber
  "#7B1FA2", // lila
  "#607D8B", // szürke-kék
  "#795548", // barna
  "#009688", // teal
  "#FF9800", // narancs
  "#673AB7", // deep-purple
];

export default function LocationStatsChart({ token }: ChartProps) {
  const year = 2024;
  const [activeTab, setActiveTab] = useState<string>("visits");
  const [locationColorMap, setLocationColorMap] = useState<
    Record<string, string>
  >({});

  // Helyszínek lekérdezése a getLocations függvénnyel
  const { data: locations = [] } = useQuery<Location[]>({
    queryKey: ["locations", token],
    queryFn: async () => {
      if (!token) throw new Error("Hiányzó token");
      return getLocations(token);
    },
    enabled: !!token,
  });

  // Utak lekérdezése a getTrips függvénnyel
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
    enabled: !!token && locations.length > 0,
  });

  // Helyszín statisztikák elkészítése
  const prepareLocationStats = (): LocationStatsItem[] => {
    // Helyszín látogatások számolása és távolságok összegzése
    const locationStats: Record<
      number,
      { visits: number; distance: number; name: string }
    > = {};

    // Inicializáljuk az összes helyszínt
    locations.forEach((location) => {
      locationStats[location.id] = {
        visits: 0,
        distance: 0,
        name: location.name,
      };
    });

    // Számoljuk az utakat
    trips.forEach((trip) => {
      // Kezdőpont látogatás
      if (locationStats[trip.start_location_id]) {
        locationStats[trip.start_location_id].visits++;
      }

      // Célpont látogatás
      if (locationStats[trip.destination_location_id]) {
        locationStats[trip.destination_location_id].visits++;

        // Távolság hozzáadása a célponthoz
        const distance =
          trip.actual_distance !== null
            ? trip.actual_distance
            : trip.end_odometer && trip.start_odometer
              ? trip.end_odometer - trip.start_odometer
              : trip.planned_distance || 0;

        locationStats[trip.destination_location_id].distance += distance;
      }
    });

    // Átalakítás lista formátumra és rendezés
    return Object.values(locationStats)
      .map((stat) => ({
        name: stat.name,
        visits: stat.visits,
        distance: Math.round(stat.distance),
      }))
      .sort(
        (a, b) =>
          b[activeTab === "visits" ? "visits" : "distance"] -
          a[activeTab === "visits" ? "visits" : "distance"]
      )
      .slice(0, 10); // Top 10 helyszín
  };

  const locationStats = prepareLocationStats();

  // Színek konzisztens hozzárendelése a helyszínekhez
  useEffect(() => {
    // Ez csak egyszer fut le, amikor betöltődnek a helyszínek
    if (locations.length > 0) {
      const uniqueLocationNames = [
        ...new Set(locations.map((loc) => loc.name)),
      ];

      const colorMap: Record<string, string> = {};
      uniqueLocationNames.forEach((name, index) => {
        colorMap[name] = LOCATION_COLORS[index % LOCATION_COLORS.length];
      });

      setLocationColorMap(colorMap);
    }
  }, [locations]);

  // Egyedi tooltip komponens
  const CustomTooltip = ({ active, payload, label }: CustomTooltipProps) => {
    if (active && payload && payload.length) {
      const data = payload[0].payload;
      return (
        <div className="bg-white dark:bg-gray-800 border shadow-lg rounded-md p-2 text-xs transform -translate-y-full">
          <h3 className="font-bold text-sm mb-1">{data.name || label}</h3>
          <div className="grid grid-cols-2 gap-x-2">
            <span>Látogatások:</span>
            <span className="font-medium text-right">{data.visits}x</span>
            <span>Távolság:</span>
            <span className="font-medium text-right">
              {formatDistance(data.distance)}
            </span>
          </div>
        </div>
      );
    }
    return null;
  };

  // Rendezett és szűrt helyszínek az egyes tab-okra
  const getTabLocationStats = (tabKey: "visits" | "distance") => {
    return [...locationStats]
      .sort((a, b) => b[tabKey] - a[tabKey])
      .slice(0, 10);
  };

  // Ha betöltés alatt van
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Helyszín statisztikák</CardTitle>
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
          <CardTitle>Helyszín statisztikák</CardTitle>
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
  if (locations.length === 0 || trips.length == 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Helyszín statisztikák</CardTitle>
          <CardDescription>2024. évi adatok</CardDescription>
        </CardHeader>
        <CardContent className="h-80 flex flex-col items-center justify-center">
          <Ban className="h-12 w-12 text-muted-foreground mb-4" />
          <div className="text-muted-foreground">
            Nincs rögzített helyszín vagy utazási adat
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="w-full pb-2">
      <CardHeader className="pb-2">
        <CardTitle>Helyszín statisztikák</CardTitle>
        <CardDescription>2024. évi helyszín adatok</CardDescription>
      </CardHeader>
      <Tabs defaultValue="visits" onValueChange={setActiveTab}>
        <div className="px-6">
          <TabsList className="grid w-full grid-cols-2">
            <TabsTrigger value="visits">Látogatások száma</TabsTrigger>
            <TabsTrigger value="distance">Megtett távolság</TabsTrigger>
          </TabsList>
        </div>
        <TabsContent value="visits">
          <CardContent className="h-80">
            {locationStats.length > 0 ? (
              <ResponsiveContainer width="100%" height={300}>
                <BarChart
                  layout="vertical"
                  data={getTabLocationStats("visits")}
                  margin={{ top: 20, right: 60, left: 30, bottom: 5 }}
                >
                  <CartesianGrid
                    strokeDasharray="3 3"
                    horizontal={true}
                    vertical={false}
                  />
                  <XAxis type="number" />
                  <YAxis
                    dataKey="name"
                    type="category"
                    width={75}
                    tick={{ fontSize: 12 }}
                    tickFormatter={(value) =>
                      value.length > 12 ? `${value.slice(0, 12)}...` : value
                    }
                  />
                  <Tooltip content={<CustomTooltip />} />
                  <Bar dataKey="visits">
                    {getTabLocationStats("visits").map((entry) => (
                      <Cell
                        key={`cell-visits-${entry.name}`}
                        fill={
                          locationColorMap[entry.name] || LOCATION_COLORS[0]
                        }
                      />
                    ))}
                  </Bar>
                </BarChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-full flex items-center justify-center text-muted-foreground">
                Nincs elegendő adat a diagram megjelenítéséhez
              </div>
            )}
          </CardContent>
        </TabsContent>
        <TabsContent value="distance">
          <CardContent className="h-80">
            {locationStats.length > 0 ? (
              <ResponsiveContainer width="100%" height={300}>
                <BarChart
                  layout="vertical"
                  data={getTabLocationStats("distance")}
                  margin={{ top: 20, right: 60, left: 30, bottom: 5 }}
                >
                  <CartesianGrid
                    strokeDasharray="3 3"
                    horizontal={true}
                    vertical={false}
                  />
                  <XAxis
                    type="number"
                    tickFormatter={(value) => `${value} km`}
                  />
                  <YAxis
                    dataKey="name"
                    type="category"
                    width={75}
                    tick={{ fontSize: 12 }}
                    tickFormatter={(value) =>
                      value.length > 12 ? `${value.slice(0, 12)}...` : value
                    }
                  />
                  <Tooltip content={<CustomTooltip />} />
                  <Bar dataKey="distance">
                    {getTabLocationStats("distance").map((entry) => (
                      <Cell
                        key={`cell-distance-${entry.name}`}
                        fill={
                          locationColorMap[entry.name] || LOCATION_COLORS[0]
                        }
                      />
                    ))}
                  </Bar>
                </BarChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-full flex items-center justify-center text-muted-foreground">
                Nincs elegendő adat a diagram megjelenítéséhez
              </div>
            )}
          </CardContent>
        </TabsContent>
      </Tabs>
      <CardFooter className="flex-col items-center -mt-4 pb-1 2xl:-mt-1 2xl:pb-3">
        <div className="text-sm text-muted-foreground">
          A top 10 leglátogatottabb vagy legnagyobb távolságú helyszínt mutatja
        </div>
      </CardFooter>
    </Card>
  );
}

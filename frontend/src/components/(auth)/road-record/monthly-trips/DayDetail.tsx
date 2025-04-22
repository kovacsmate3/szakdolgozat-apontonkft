"use client";

import { format } from "date-fns";
import { hu } from "date-fns/locale";
import { Trip } from "@/lib/types";
import {
  Clock,
  MapPin,
  Car,
  User,
  ArrowRight,
  CalendarClock,
  Pencil,
  Trash2,
} from "lucide-react";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { calculateTotalDistance, roundToTwoDecimals } from "@/lib/functions";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";

interface DayDetailProps {
  day: Date;
  trips: Trip[];
}

export function DayDetail({ day, trips }: DayDetailProps) {
  const totalDistance = calculateTotalDistance(trips);

  // Sort trips by start time
  const sortedTrips = [...trips].sort(
    (a, b) =>
      new Date(a.start_time).getTime() - new Date(b.start_time).getTime()
  );

  if (trips.length === 0) {
    return (
      <div className="p-6 flex flex-col items-center justify-center space-y-4 min-h-[300px] border rounded-md bg-muted/10">
        <CalendarClock className="h-12 w-12 text-muted-foreground" />
        <p className="text-muted-foreground text-center max-w-md">
          Ezen a napon ({format(day, "yyyy. MMMM d.", { locale: hu })}) nem
          található rögzített utazás.
        </p>
        <Button className="mt-2" asChild>
          <Link href="/road-record/add-trip">Út hozzáadása</Link>
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h3 className="text-lg font-medium">
            {format(day, "yyyy. MMMM d., EEEE", { locale: hu })}
          </h3>
          <p className="text-sm text-muted-foreground">
            Összesen: {trips.length} utazás, {roundToTwoDecimals(totalDistance)}{" "}
            km
          </p>
        </div>
        <Button className="bg-primary text-primary-foreground" asChild>
          + Út hozzáadása
        </Button>
      </div>

      <Card className="overflow-hidden">
        <CardHeader className="bg-muted/30 py-3">
          <CardTitle className="text-base font-medium">Napi utak</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-[100px]">Időpont</TableHead>
                  <TableHead>Útvonal</TableHead>
                  <TableHead>Távolság</TableHead>
                  <TableHead>Eszköz</TableHead>
                  <TableHead>Utazás célja</TableHead>
                  <TableHead className="text-center">Műveletek</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {sortedTrips.map((trip) => (
                  <TableRow key={trip.id}>
                    <TableCell className="font-medium whitespace-nowrap">
                      <div className="flex items-center gap-1">
                        <Clock className="h-3 w-3 text-muted-foreground" />
                        <span>
                          {format(new Date(trip.start_time), "HH:mm")}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1 font-medium">
                        <MapPin className="h-3 w-3 text-muted-foreground" />
                        <span
                          className="max-w-[120px] md:max-w-[200px] truncate"
                          title={trip.start_location?.name || "Ismeretlen"}
                        >
                          {trip.start_location?.name || "Ismeretlen"}
                        </span>
                        <ArrowRight className="h-3 w-3 mx-1" />
                        <span
                          className="max-w-[120px] md:max-w-[200px] truncate"
                          title={
                            trip.destination_location?.name || "Ismeretlen"
                          }
                        >
                          {trip.destination_location?.name || "Ismeretlen"}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell>
                      {trip.actual_distance
                        ? `${trip.actual_distance} km`
                        : "-"}
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1">
                        <Car className="h-3 w-3 text-muted-foreground" />
                        <span
                          className="max-w-[150px] truncate"
                          title={
                            trip.car
                              ? `${trip.car.model} (${trip.car.license_plate})`
                              : "Ismeretlen"
                          }
                        >
                          {trip.car
                            ? `${trip.car.model} (${trip.car.license_plate})`
                            : "Ismeretlen"}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1">
                        <User className="h-3 w-3 text-muted-foreground" />
                        <span>
                          {trip.travel_purpose?.travel_purpose ||
                            "Nem megadott"}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-1 whitespace-nowrap">
                        <Link href={`/road-record/trips/${trip.id}`}>
                          <Button type="button" variant="outline">
                            <Pencil className="h-4 w-4" />
                            Szerkesztés
                          </Button>
                        </Link>
                        <Button type="submit" variant="destructive">
                          <Trash2 className="h-4 w-4" />
                          Törlés
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

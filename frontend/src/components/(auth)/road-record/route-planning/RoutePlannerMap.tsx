"use client";

import { useMemo, useRef, useState } from "react";
import { GoogleMap, DirectionsRenderer } from "@react-google-maps/api";
import {
  Select,
  SelectTrigger,
  SelectContent,
  SelectItem,
  SelectValue,
} from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Address, Car, FuelPrice } from "@/lib/types";
import {
  calculateFuelCost,
  formatDurationHU,
  getFullAddress,
  getFullAddressWithCountry,
  getLatestFuelPrice,
} from "@/lib/functions";
import { GiPathDistance } from "react-icons/gi";
import { FaCarSide, FaMapMarkerAlt } from "react-icons/fa";
import { FuelCostSection } from "./FuelCostSection";
import { MdOutlineTimer } from "react-icons/md";

const containerStyle = {
  width: "100%",
  height: "500px",
  borderRadius: "12px",
};

interface Props {
  addresses: Address[];
  cars: Car[];
  fuelPrices: FuelPrice[];
}

const RoutePlannerMap = ({ addresses, cars, fuelPrices }: Props) => {
  const [startAddress, setStartAddress] = useState<string>();
  const [endAddress, setEndAddress] = useState<string>();
  const [sameAddressError, setSameAddressError] = useState(false);
  const [startCoords, setStartCoords] =
    useState<google.maps.LatLngLiteral | null>(null);
  const [directions, setDirections] =
    useState<google.maps.DirectionsResult | null>(null);

  const mapRef = useRef<google.maps.Map | null>(null);

  const [distanceText, setDistanceText] = useState<string | null>(null);
  const [durationText, setDurationText] = useState<string | null>(null);
  const [distanceKm, setDistanceKm] = useState<number | null>(null);
  const [durationFormatted, setDurationFormatted] = useState<string | null>(
    null
  );

  const [selectedCarId, setSelectedCarId] = useState<string>();
  const [fuelCost, setFuelCost] = useState<number | null>(null);

  // Az aktuálisan aktív (kiszámított) állapot
  const [activeCarId, setActiveCarId] = useState<string>();

  // A legfrissebb üzemanyagár kiválasztása (legújabb dátumú)
  const latestFuelPrice = useMemo(
    () => getLatestFuelPrice(fuelPrices),
    [fuelPrices]
  );

  // Kiválasztott autó adatai
  const selectedCar = useMemo(() => {
    if (!selectedCarId) return null;
    return cars.find((car) => car.id.toString() === selectedCarId) || null;
  }, [selectedCarId, cars]);

  // Az aktív autó az, amelyikre a legutolsó számítás történt
  const activeCar = useMemo(() => {
    if (!activeCarId) return null;
    return cars.find((car) => car.id.toString() === activeCarId) || null;
  }, [activeCarId, cars]);

  // Figyeli a kezdő- és végpont változását, beállítja a hiba állapotot ha megegyeznek
  const validateAddresses = (start?: string, end?: string) => {
    setSameAddressError(!!start && !!end && start === end);
  };

  const geocodeAddress = async (
    address: string
  ): Promise<google.maps.LatLngLiteral | null> => {
    try {
      const res = await fetch(
        `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY}`
      );
      const data = await res.json();
      if (data.status === "OK" && data.results.length > 0) {
        const loc = data.results[0].geometry.location;
        return { lat: loc.lat, lng: loc.lng };
      }
      return null;
    } catch (error) {
      console.error("Geocoding hiba:", error);
      return null;
    }
  };

  const handleRoute = async () => {
    if (!startAddress || !endAddress || sameAddressError) return;

    try {
      const [from, to] = await Promise.all([
        geocodeAddress(startAddress),
        geocodeAddress(endAddress),
      ]);

      if (!from || !to) {
        alert("Nem sikerült megtalálni a cím(eke)t.");
        return;
      }

      setStartCoords(from);

      const directionsService = new google.maps.DirectionsService();

      directionsService.route(
        {
          origin: from,
          destination: to,
          travelMode: google.maps.TravelMode.DRIVING,
        },
        (result, status) => {
          if (status === "OK" && result) {
            setDirections(result);

            const bounds = new google.maps.LatLngBounds();
            result.routes[0].overview_path.forEach((p) => bounds.extend(p));
            mapRef.current?.fitBounds(bounds);

            const leg = result.routes[0].legs[0];
            setDistanceText(leg.distance?.text || null);
            setDurationText(leg.duration?.text || null);

            const distanceKmValue = leg.distance?.value
              ? leg.distance.value / 1000
              : null;
            const durationSeconds = leg.duration?.value || 0;

            const durationFormatted = formatDurationHU(durationSeconds);

            setDistanceKm(distanceKmValue);
            setDurationFormatted(durationFormatted);

            // Frissítjük az aktív autót a jelenleg kiválasztott autóra
            setActiveCarId(selectedCarId);

            // Üzemanyagköltség számítása
            if (distanceKmValue && selectedCar) {
              const cost = calculateFuelCost(
                distanceKmValue,
                selectedCar,
                latestFuelPrice
              );
              setFuelCost(cost);
            } else {
              setFuelCost(null);
            }
          } else {
            console.error("Directions error:", status);
          }
        }
      );
    } catch (err) {
      console.error("Útvonaltervezési hiba:", err);
    }
  };

  return (
    <Card className="w-full p-4">
      <CardContent className="grid gap-6 lg:grid-cols-2">
        {/* Bal oldal – vezérlés */}
        <div className="flex flex-col items-center space-y-4 text-center my-auto">
          <div className="w-full max-w-sm">
            <Label htmlFor="car" className="mb-2 block text-left">
              Autó kiválasztása
            </Label>
            <Select onValueChange={setSelectedCarId}>
              <SelectTrigger id="car" className="w-full">
                <SelectValue placeholder="Válassz autót" />
              </SelectTrigger>
              <SelectContent className="z-[100]">
                {cars.map((car) => (
                  <SelectItem key={car.id} value={car.id.toString()}>
                    <span className="flex items-center gap-2">
                      <FaCarSide className="text-muted-foreground" />
                      {car.manufacturer} {car.model} ({car.license_plate})
                    </span>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="w-full max-w-sm">
            <Label htmlFor="start" className="mb-2 block text-left">
              Kiindulópont
            </Label>
            <Select
              onValueChange={(value) => {
                setStartAddress(value);
                validateAddresses(value, endAddress);
              }}
            >
              <SelectTrigger id="start" className="w-full">
                <SelectValue placeholder="Válassz kiindulópontot" />
              </SelectTrigger>
              <SelectContent className="z-[100]">
                {addresses.map((addr) => (
                  <SelectItem
                    key={addr.id}
                    value={getFullAddressWithCountry(addr)}
                  >
                    <span className="flex items-center gap-2">
                      <FaMapMarkerAlt className="text-muted-foreground" />
                      {getFullAddress(addr)}
                    </span>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="w-full max-w-sm">
            <Label htmlFor="end" className="mb-2 block text-left">
              Célállomás
            </Label>
            <Select
              onValueChange={(value) => {
                setEndAddress(value);
                validateAddresses(startAddress, value);
              }}
            >
              <SelectTrigger id="end" className="w-full">
                <SelectValue placeholder="Válassz célállomást" />
              </SelectTrigger>
              <SelectContent className="z-[100]">
                {addresses.map((addr) => (
                  <SelectItem
                    key={addr.id}
                    value={getFullAddressWithCountry(addr)}
                  >
                    <span className="flex items-center gap-2">
                      <FaMapMarkerAlt className="text-muted-foreground" />
                      {getFullAddress(addr)}
                    </span>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <Button
            onClick={handleRoute}
            disabled={!startAddress || !endAddress || sameAddressError}
          >
            Tervezés
          </Button>

          {sameAddressError && (
            <p className="text-red-400 text-sm">
              A kiindulópont és célállomás nem lehet ugyanaz!
            </p>
          )}

          {(distanceText || durationText) && (
            <div className="p-4 rounded-md bg-muted text-foreground border mt-2 w-full max-w-sm text-center">
              <div className="flex flex-col gap-2">
                {distanceKm && (
                  <p className="flex items-center justify-center gap-2">
                    <GiPathDistance className="size-4" />
                    <span>
                      <strong>Távolság:</strong> {distanceKm.toFixed(2)} km
                    </span>
                  </p>
                )}
                {durationFormatted && (
                  <p className="flex items-center justify-center gap-2">
                    <MdOutlineTimer className="size-4" />
                    <span>
                      <strong>Becsült idő:</strong> {durationFormatted}
                    </span>
                  </p>
                )}
                {/* A költségbecslés kiszervezett komponensben */}
                <FuelCostSection
                  fuelCost={fuelCost}
                  selectedCar={activeCar}
                  latestFuelPrice={latestFuelPrice}
                />
              </div>
            </div>
          )}
        </div>

        {/* Jobb oldal – térkép */}
        <div className="h-full w-full">
          <GoogleMap
            mapContainerStyle={containerStyle}
            center={startCoords || { lat: 47.4979, lng: 19.0402 }}
            zoom={12}
            onLoad={(map) => {
              mapRef.current = map;
            }}
          >
            {directions && <DirectionsRenderer directions={directions} />}
          </GoogleMap>
        </div>
      </CardContent>
    </Card>
  );
};
export default RoutePlannerMap;

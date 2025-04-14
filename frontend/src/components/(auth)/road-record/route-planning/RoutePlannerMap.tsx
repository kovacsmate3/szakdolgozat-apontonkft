"use client";

import { useEffect, useRef, useState } from "react";
import { getSession } from "next-auth/react";
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
import { Address } from "@/lib/types";

const containerStyle = {
  width: "100%",
  height: "500px",
  borderRadius: "12px",
};

const RoutePlannerMap = () => {
  const [addresses, setAddresses] = useState<Address[]>([]);
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

  const formatAddressOption = (address: Address): string => {
    return (
      address.location?.name ??
      `${address.postalcode} ${address.city}, ${address.road_name} ${address.public_space_type} ${address.building_number}`
    );
  };

  const getFullAddressString = (addr: Address): string => {
    return `${addr.country}, ${addr.postalcode} ${addr.city}, ${addr.road_name} ${addr.public_space_type} ${addr.building_number}`;
  };

  useEffect(() => {
    const fetchAddresses = async () => {
      const session = await getSession();
      const token = session?.access_token;

      if (!token) {
        console.error("Nincs token!");
        return;
      }

      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/addresses`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!res.ok) {
        console.error("Címek lekérése sikertelen.");
        return;
      }

      const data = await res.json();
      setAddresses(data);
    };

    fetchAddresses();
  }, []);

  useEffect(() => {
    setSameAddressError(startAddress === endAddress && !!startAddress);
  }, [startAddress, endAddress]);

  const geocodeAddress = async (
    address: string
  ): Promise<google.maps.LatLngLiteral | null> => {
    const res = await fetch(
      `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY}`
    );
    const data = await res.json();
    if (data.status === "OK" && data.results.length > 0) {
      const loc = data.results[0].geometry.location;
      return { lat: loc.lat, lng: loc.lng };
    }
    return null;
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
            const hours = Math.floor(durationSeconds / 3600);
            const minutes = Math.floor((durationSeconds % 3600) / 60);
            const seconds = durationSeconds % 60;

            const durationFormatted = `${hours.toString().padStart(2, "0")}:${minutes
              .toString()
              .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;

            setDistanceKm(distanceKmValue);
            setDurationFormatted(durationFormatted);

            console.log(`Távolság (km): ${distanceKmValue}`);
            console.log(`Időtartam: ${durationFormatted}`);
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
            <Label htmlFor="start" className="mb-2 block text-left">
              Kiindulópont
            </Label>
            <Select onValueChange={setStartAddress}>
              <SelectTrigger id="start" className="w-full">
                <SelectValue placeholder="Válassz kiindulópontot" />
              </SelectTrigger>
              <SelectContent className="z-[100]">
                {addresses.map((addr) => (
                  <SelectItem key={addr.id} value={getFullAddressString(addr)}>
                    {formatAddressOption(addr)}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="w-full max-w-sm">
            <Label htmlFor="end" className="mb-2 block text-left">
              Célállomás
            </Label>
            <Select onValueChange={setEndAddress}>
              <SelectTrigger id="end" className="w-full">
                <SelectValue placeholder="Válassz célállomást" />
              </SelectTrigger>
              <SelectContent className="z-[100]">
                {addresses.map((addr) => (
                  <SelectItem key={addr.id} value={getFullAddressString(addr)}>
                    {formatAddressOption(addr)}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <Button
            onClick={handleRoute}
            disabled={!startAddress || !endAddress || sameAddressError}
          >
            Útvonal megjelenítése
          </Button>

          {sameAddressError && (
            <p className="text-red-400 text-sm">
              A kiindulópont és célállomás nem lehet ugyanaz!
            </p>
          )}

          {(distanceText || durationText) && (
            <div className="p-4 rounded-md bg-muted text-sm text-foreground border mt-2 w-full max-w-sm text-center">
              {distanceKm && (
                <p>
                  <strong>Távolság:</strong>{" "}
                  {distanceKm && ` ${distanceKm.toFixed(2)} km`}
                </p>
              )}
              {durationFormatted && (
                <p>
                  <strong>Becsült idő:</strong> {durationFormatted}
                </p>
              )}
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

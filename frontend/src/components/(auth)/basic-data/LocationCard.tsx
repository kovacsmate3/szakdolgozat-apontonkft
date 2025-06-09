"use client";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Building, Handshake, MapPin, PenSquare, Trash2 } from "lucide-react";
import { PiOfficeChairFill } from "react-icons/pi";
import { FaGasPump, FaShoppingCart } from "react-icons/fa";
import { MdOtherHouses } from "react-icons/md";
import { Badge } from "@/components/ui/badge";
import { IconType } from "react-icons";
import { getFullAddress } from "@/lib/functions";
import { Location } from "@/lib/types";
import { Button } from "@/components/ui/button";

// Mapping of location types to icons
const locationTypeIcons: Record<string, IconType | typeof Building> = {
  telephely: PiOfficeChairFill,
  partner: Handshake,
  töltőállomás: FaGasPump,
  bolt: FaShoppingCart,
  egyéb: MdOtherHouses,
  // Fallback to default Building icon
  default: Building,
};

interface LocationCardProps {
  location: Location;
  isAdmin: boolean;
  currentUserId: number;
  onEdit: (location: Location) => void;
  onDelete: (location: Location) => void;
}

export function LocationCard({
  location,
  isAdmin,
  currentUserId,
  onEdit,
  onDelete,
}: LocationCardProps) {
  const LocationIcon =
    locationTypeIcons[location.location_type.toLowerCase()] ||
    locationTypeIcons["default"];

  // Ellenőrizzük, hogy a felhasználó szerkesztheti-e a helyszínt
  // (Admin vagy a helyszín létrehozója)
  const canEdit = isAdmin || location.user_id === currentUserId;

  return (
    <Card className="hover:shadow-md transition-shadow">
      <CardHeader className="flex items-center justify-between pb-2">
        <CardTitle className="md:text-lg flex items-center gap-2">
          <LocationIcon className="size-5 text-muted-foreground" />
          {location.name}
        </CardTitle>
        <div className="flex items-center gap-2">
          {canEdit && (
            <>
              <Button
                variant="ghost"
                size="icon"
                onClick={() => onEdit(location)}
                className="h-8 w-8"
              >
                <PenSquare className="h-4 w-4" />
                <span className="sr-only">Szerkesztés</span>
              </Button>
              <Button
                variant="ghost"
                size="icon"
                onClick={() => onDelete(location)}
                className="h-8 w-8"
              >
                <Trash2 className="h-4 w-4" />
                <span className="sr-only">Törlés</span>
              </Button>
            </>
          )}
        </div>
      </CardHeader>
      {location.address && (
        <CardContent>
          <div className="flex items-center justify-between text-sm text-muted-foreground">
            <div className="flex items-center">
              <MapPin className="size-4 mr-2 flex-shrink-0" />
              <span>{getFullAddress(location.address)}</span>
            </div>
            {location.is_headquarter && (
              <Badge
                variant="secondary"
                className="bg-green-100 text-green-800"
              >
                <Building className="size-4" />
                Székhely
              </Badge>
            )}
          </div>
        </CardContent>
      )}
    </Card>
  );
}

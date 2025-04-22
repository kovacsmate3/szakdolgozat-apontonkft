import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Building, Handshake, MapPin } from "lucide-react";
import { PiOfficeChairFill } from "react-icons/pi";
import { FaGasPump, FaShoppingCart } from "react-icons/fa";
import { MdOtherHouses } from "react-icons/md";
import { Badge } from "@/components/ui/badge";
import { IconType } from "react-icons";
import { getFullAddress } from "@/lib/functions";
import { Location } from "@/lib/types";

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
}

export function LocationCard({ location }: LocationCardProps) {
  const LocationIcon =
    locationTypeIcons[location.location_type.toLowerCase()] ||
    locationTypeIcons["default"];

  return (
    <Card className="hover:shadow-md transition-shadow">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-lg flex items-center gap-2">
          <LocationIcon className="size-5 text-muted-foreground" />
          {location.name}
        </CardTitle>
        {location.is_headquarter && (
          <Badge variant="secondary" className="bg-green-100 text-green-800">
            Székhely
          </Badge>
        )}
      </CardHeader>
      {location.address && (
        <CardContent>
          <div className="flex items-center text-sm text-muted-foreground">
            <MapPin className="size-4 mr-2 flex-shrink-0" />
            <span className="">{getFullAddress(location.address)}</span>
          </div>
        </CardContent>
      )}
    </Card>
  );
}

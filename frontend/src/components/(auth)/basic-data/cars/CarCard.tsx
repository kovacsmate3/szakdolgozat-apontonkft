import Image from "next/image";
import { useState } from "react";
import CarDetails from "./CarDetails";
import { CarComponentProps } from "@/lib/types";
import { Button } from "@/components/ui/button";
import { FaGasPump, FaTachometerAlt } from "react-icons/fa";
import { useTheme } from "next-themes";
import { SquarePen, Trash2 } from "lucide-react";

interface ExtendedCarComponentProps extends CarComponentProps {
  token: string;
  isAdmin: boolean;
  currentUserId: number;
  onEdit: (car: CarComponentProps["car"]) => void;
  onDelete: (car: CarComponentProps["car"]) => void;
}

const CarCard = ({
  car,
  isAdmin,
  currentUserId,
  onEdit,
  onDelete,
}: ExtendedCarComponentProps) => {
  const [isDetailsOpen, setIsDetailsOpen] = useState(false);
  const { resolvedTheme } = useTheme();

  const imageSrc =
    resolvedTheme === "dark"
      ? "/images/(auth)/basic-data/cars/car-placeholder-light.png"
      : "/images/(auth)/basic-data/cars/car-placeholder-dark.png";

  // Ellenőrizzük, hogy a felhasználó jogosult-e a szerkesztésre/törlésre
  const canModify = isAdmin || car.user_id === currentUserId;

  return (
    <div className="group p-4 rounded-lg border bg-background shadow-sm">
      <div className="flex justify-between items-start">
        <div>
          <h2 className="text-lg font-semibold">
            {car.manufacturer} {car.model}
          </h2>
          <p className="text-muted-foreground text-sm">{car.license_plate}</p>
        </div>
        {canModify && (
          <div className="flex items-center gap-2">
            <Button
              variant="ghost"
              size="icon"
              className="h-8 w-8 p-0 text-muted-foreground hover:text-primary"
              onClick={() => onEdit(car)}
            >
              <SquarePen className="h-4 w-4" />
              <span className="sr-only">Szerkesztés</span>
            </Button>
            <Button
              variant="ghost"
              size="icon"
              className="h-8 w-8 p-0 text-destructive hover:text-destructive/80"
              onClick={() => onDelete(car)}
            >
              <Trash2 className="h-4 w-4" />
              <span className="sr-only">Törlés</span>
            </Button>
          </div>
        )}
      </div>

      <div className="relative w-full h-40 my-2 rounded-md bg-muted overflow-hidden">
        <Image
          src={imageSrc}
          alt={`${car.manufacturer} ${car.model}`}
          fill
          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
          priority
          className="object-contain"
        />
      </div>

      <div className="flex justify-between items-center text-sm text-muted-foreground">
        <p className="flex items-center gap-1">
          <FaTachometerAlt className="text-base" />
          <span className="font-medium">
            {car.standard_consumption} l/100km
          </span>
        </p>
        <p className="flex items-center gap-1">
          <FaGasPump className="text-base" />
          <span className="capitalize">{car.fuel_type}</span>
        </p>
      </div>

      <Button
        onClick={() => setIsDetailsOpen(true)}
        className="mt-3 w-full py-2 rounded-md"
      >
        Részletek
      </Button>

      <CarDetails
        isOpen={isDetailsOpen}
        closeModal={() => setIsDetailsOpen(false)}
        car={car}
      />
    </div>
  );
};

export default CarCard;

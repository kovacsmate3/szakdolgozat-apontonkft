import Image from "next/image";
import { useState } from "react";
import CarDetails from "./CarDetails";
import { CarComponentProps } from "@/lib/types";
import { Button } from "@/components/ui/button";
import { FaGasPump, FaTachometerAlt } from "react-icons/fa";
import { useTheme } from "next-themes";
import { SquarePen } from "lucide-react";

const CarCard = ({ car }: CarComponentProps) => {
  const [isOpen, setIsOpen] = useState(false);
  const { resolvedTheme } = useTheme();

  const imageSrc =
    resolvedTheme === "dark"
      ? "/images/(auth)/basic-data/cars/car-placeholder-light.png"
      : "/images/(auth)/basic-data/cars/car-placeholder-dark.png";

  return (
    <div className="group p-4 rounded-lg border bg-background shadow-sm">
      <div className="flex justify-between items-start">
        <div>
          <h2 className="text-lg font-semibold">
            {car.manufacturer} {car.model}
          </h2>
          <p className="text-muted-foreground text-sm">{car.license_plate}</p>
        </div>
        <Button
          variant="ghost"
          size="icon"
          className="h-8 w-8 p-0 text-muted-foreground hover:text-primary"
          onClick={() => {
            console.log("Szerkesztés megnyitása:", car);
          }}
        >
          <SquarePen className="h-4 w-4" />
          <span className="sr-only">Szerkesztés</span>
        </Button>
      </div>

      <div className="relative w-full h-40 my-2 rounded-md bg-muted overflow-hidden   ">
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
        onClick={() => setIsOpen(true)}
        className="mt-3 w-full py-2 rounded-md"
      >
        Részletek
      </Button>

      <CarDetails
        isOpen={isOpen}
        closeModal={() => setIsOpen(false)}
        car={car}
      />
    </div>
  );
};

export default CarCard;

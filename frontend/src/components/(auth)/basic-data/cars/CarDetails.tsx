"use client";

import Image from "next/image";
import { Car } from "@/lib/types";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { FaGasPump, FaIdCard, FaTachometerAlt } from "react-icons/fa";
import { FaBatteryFull, FaCar } from "react-icons/fa6";
import { PiEngineBold } from "react-icons/pi";
import { useTheme } from "next-themes";

interface Props {
  isOpen: boolean;
  closeModal: () => void;
  car: Car;
}

const CarDetails = ({ isOpen, closeModal, car }: Props) => {
  const { resolvedTheme } = useTheme();

  const imageSrc =
    resolvedTheme === "dark"
      ? "/images/(auth)/basic-data/cars/car-placeholder-light.png"
      : "/images/(auth)/basic-data/cars/car-placeholder-dark.png";

  return (
    <Dialog open={isOpen} onOpenChange={closeModal}>
      <DialogContent className="max-w-md">
        <DialogHeader>
          <DialogTitle>
            {car.manufacturer} {car.model}
          </DialogTitle>
          <DialogDescription className="sr-only">
            Részletes információk a kiválasztott járműről
          </DialogDescription>
        </DialogHeader>

        <div className="relative w-full h-40 bg-muted rounded-md overflow-hidden">
          <Image
            src={imageSrc}
            alt={`${car.manufacturer} ${car.model}`}
            fill
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
            priority
            className="object-contain"
          />
        </div>

        <div className="grid grid-cols-2 gap-4 mt-4 text-sm text-muted-foreground">
          <div className="flex items-center gap-2">
            <FaIdCard />
            <span>
              <strong>Rendszám:</strong> {car.license_plate}
            </span>
          </div>
          <div className="flex items-center gap-2">
            <FaCar />
            <span>
              <strong>Típus:</strong> {car.car_type}
            </span>
          </div>
          <div className="flex items-center gap-2">
            <FaGasPump />
            <span>
              <strong>Üzemanyag:</strong> {car.fuel_type}
            </span>
          </div>
          <div className="flex items-center gap-2">
            <FaTachometerAlt />
            <span>
              <strong>Fogyasztás:</strong> {car.standard_consumption} l/100km
            </span>
          </div>
          <div className="flex items-center gap-2">
            <PiEngineBold />
            <span>
              <strong>Motortérfogat:</strong> {car.capacity} cm³
            </span>
          </div>
          <div className="flex items-center gap-2">
            <FaBatteryFull />
            <span>
              <strong>Tartály:</strong> {car.fuel_tank_capacity} l
            </span>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default CarDetails;

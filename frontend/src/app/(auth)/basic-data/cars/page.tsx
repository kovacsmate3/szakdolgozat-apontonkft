// app/(dashboard)/basic-data/cars/page.tsx

"use client";

import { useEffect, useState } from "react";
import { getSession } from "next-auth/react";
import { Car } from "@/lib/types";
import CarCard from "@/components/(auth)/basic-data/cars/CarCard";

export default function CarsPage() {
  const [cars, setCars] = useState<Car[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchCars = async () => {
      const session = await getSession();
      const token = session?.access_token;

      if (!token) {
        console.error("Nincs érvényes token.");
        return;
      }

      try {
        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/cars`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        if (!res.ok) throw new Error("Sikertelen lekérés");

        const data = await res.json();
        setCars(data);
      } catch (err) {
        console.error("Hiba a lekérés során:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchCars();
  }, []);

  if (loading) {
    return <p className="text-center mt-6">Autók betöltése...</p>;
  }

  return (
    <main className="p-6">
      <h1 className="text-2xl font-bold mb-6">Autók</h1>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {cars.map((car) => (
          <CarCard key={car.id} car={car} />
        ))}
      </div>
    </main>
  );
}

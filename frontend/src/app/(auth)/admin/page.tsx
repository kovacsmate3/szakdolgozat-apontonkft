"use client";

import Link from "next/link";
import { FaUserShield } from "react-icons/fa6";
import { Users } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

export default function AdminPage() {
  return (
    <div className="container mx-auto py-10">
      <div className="flex items-center gap-3 mb-8">
        <FaUserShield className="size-8 text-primary" />
        <h1 className="text-3xl font-bold">Adminisztráció</h1>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card className="hover:shadow-lg transition-shadow">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-xl">Felhasználók</CardTitle>
            <Users className="size-6 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground mb-4">
              Rendszerfelhasználók kezelése, létrehozása, szerkesztése és
              jogosultságaik beállítása
            </p>
            <Button asChild variant="outline" className="w-full">
              <Link href="/admin/users">Megnyitás</Link>
            </Button>
          </CardContent>
        </Card>
      </div>

      <div className="mt-10">
        <h2 className="text-2xl font-semibold mb-4">
          Adminisztrációs funkciók
        </h2>
        <div className="prose max-w-none leading-relaxed text-sm md:text-base text-justify">
          <p>
            Az Adminisztráció modul az A-Ponton Mérnökiroda Kft. rendszerének
            központi irányítását teszi lehetővé. Adminisztrátori jogosultsággal
            rendelkező felhasználók itt kezelhetik a rendszer felhasználóit és
            beállításait.
          </p>

          <p>Az Adminisztráció fő funkciója:</p>

          <ul className="list-disc pl-4">
            <li>
              <strong>Felhasználók kezelése:</strong> Új felhasználók
              létrehozása, meglévő felhasználók adatainak módosítása,
              felhasználói fiókok aktiválása/inaktiválása. Az adminisztrátorok
              itt állíthatják be a felhasználók jogosultságait és szerepköreit
              is, ezzel szabályozva, hogy ki milyen funkciókat érhet el a
              rendszerben.
            </li>
          </ul>

          <p className="mt-4 text-justify">
            Az adminisztrációs funkciók használata jelentős felelősséggel jár,
            mivel a rendszer működésének alapvető aspektusait befolyásolja. Csak
            megfelelő képzéssel és felhatalmazással rendelkező felhasználók
            számára javasolt ezeknek a funkcióknak a használata.
          </p>

          <div className="bg-amber-50 dark:bg-amber-950/30 p-4 rounded-md border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm mt-4">
            <p className="mt-0 text-justify">
              <strong>Fontos:</strong> Az adminisztrációs felület kritikus
              rendszerbeállításokat tartalmaz, amelyek az egész rendszer
              működését befolyásolják. Kérjük, felelősségteljesen használja
              ezeket a funkciókat.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

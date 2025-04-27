"use client";

import Link from "next/link";
import { FaRoad } from "react-icons/fa";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { roadRecordSections } from "@/lib/data/road-record-dashboard";

export default function RoadRecordPage() {
  return (
    <div className="container mx-auto py-10">
      <div className="flex items-center gap-3 mb-8">
        <FaRoad className="size-8 text-primary" />
        <h1 className="text-3xl font-bold">Útnyilvántartás</h1>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {roadRecordSections.map((section) => (
          <Card
            key={section.title}
            className="hover:shadow-lg transition-shadow"
          >
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-xl">{section.title}</CardTitle>
              <section.icon className="size-6 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground mb-4">
                {section.description}
              </p>
              <Button asChild variant="outline" className="w-full">
                <Link href={section.href}>Megnyitás</Link>
              </Button>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="mt-10">
        <h2 className="text-2xl font-semibold mb-4">
          Az Útnyilvántartás modul használata
        </h2>
        <div className="prose max-w-none leading-relaxed text-sm md:text-base text-justify">
          <p>
            Az Útnyilvántartás modul lehetővé teszi a céges és magán utazások
            részletes nyilvántartását, a tankolások követését, valamint
            útvonalak előzetes tervezését. A rendszer segít a hivatalos
            útnyilvántartások vezetésében, az üzemanyag-elszámolások
            elkészítésében, valamint a különböző előírásoknak való
            megfelelésben.
          </p>

          <p>Az Útnyilvántartás három fő részből áll:</p>

          <ul className="list-disc pl-4">
            <li>
              <strong>Havi utak:</strong> A havi naptár nézetben rögzítheti és
              áttekintheti az összes utazást, részletes információkkal mint
              indulási és érkezési hely, időpont, megtett távolság. Az utazások
              egyszerűen exportálhatók hivatalos dokumentumok elkészítéséhez.
            </li>
            <li>
              <strong>Tankolások/Töltések:</strong> Rögzítheti a tankolások
              részleteit, ideértve az üzemanyag mennyiségét, árát, a tankolás
              helyét és idejét. A rendszer automatikusan kiszámítja a
              fogyasztási adatokat, és statisztikákat készít a költségekről.
            </li>
            <li>
              <strong>Útvonaltervezés:</strong> Tervezze meg előre az
              útvonalait, kiszámítva a várható távolságot és költségeket. Az
              útvonaltervező segít optimalizálni a munkautak szervezését,
              valamint előre kalkulálni az üzemanyag-költségeket.
            </li>
          </ul>

          <p className="mt-4 text-justify">
            A megfelelően vezetett útnyilvántartás nemcsak a jogszabályi
            kötelezettségek teljesítésében segít, hanem hozzájárul a
            költséghatékony működéshez, az utazások optimalizálásához és a céges
            járművek használatának átláthatóságához is.
          </p>
        </div>
      </div>
    </div>
  );
}

"use client";

import { useState } from "react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Card, CardContent } from "@/components/ui/card";
import { AlertCircle, FileText, FileSpreadsheet, Loader2 } from "lucide-react";
import { format } from "date-fns";
import { hu } from "date-fns/locale";
import { Car } from "@/lib/types";
import { exportTripsToDoc, exportTripsToExcel } from "@/server/trips";

interface ExportTripsDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  token: string;
  cars: Car[];
  year: number;
  month: number;
}

export function ExportTripsDialog({
  open,
  onOpenChange,
  token,
  cars,
  year,
  month,
}: ExportTripsDialogProps) {
  const [selectedCarId, setSelectedCarId] = useState<string>(
    cars.length === 1 ? String(cars[0].id) : ""
  );
  const [isExporting, setIsExporting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [exportTab, setExportTab] = useState<"monthly" | "yearly">("monthly");
  const [monthlyExportType, setMonthlyExportType] = useState<"word" | "excel">(
    "word"
  );

  const monthName = format(new Date(year, month - 1, 1), "MMMM", {
    locale: hu,
  });

  // Hibaüzenet törlése tab váltáskor
  const handleTabChange = (value: string) => {
    setExportTab(value as "monthly" | "yearly");
    setError(null); // Hibaüzenet törlése
  };

  // Hibaüzenet törlése export típus váltáskor
  const handleExportTypeChange = (type: "word" | "excel") => {
    setMonthlyExportType(type);
    setError(null); // Hibaüzenet törlése
  };

  // Hibaüzenet törlése autó kiválasztásakor
  const handleCarChange = (value: string) => {
    setSelectedCarId(value);
    setError(null); // Hibaüzenet törlése
  };

  const handleExport = async () => {
    if (!selectedCarId) {
      setError("Kérjük, válasszon egy járművet az exportáláshoz.");
      return;
    }

    setIsExporting(true);
    setError(null);

    try {
      let blob: Blob;

      if (exportTab === "monthly") {
        if (monthlyExportType === "word") {
          // Havi Word dokumentum exportálása
          blob = await exportTripsToDoc({
            token,
            car_id: parseInt(selectedCarId),
            year,
            month,
          });
        } else {
          // Havi Excel dokumentum exportálása
          blob = await exportTripsToExcel({
            token,
            car_id: parseInt(selectedCarId),
            year,
            month,
          });
        }
      } else {
        // Éves Excel dokumentum exportálása
        blob = await exportTripsToExcel({
          token,
          car_id: parseInt(selectedCarId),
          year,
        });
      }
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      const selectedCar = cars.find(
        (car) => car.id === parseInt(selectedCarId)
      );
      const license_plate = selectedCar?.license_plate || "auto";

      a.href = url;
      // A fájl kiterjesztése és neve a választott export típustól függ
      const extension =
        exportTab === "monthly" && monthlyExportType === "word"
          ? "docx"
          : "xlsx";
      let fileName = "";

      if (exportTab === "monthly") {
        fileName = `utnyilvantartas_${license_plate}_${year}_${month}.${extension}`;
      } else {
        fileName = `utnyilvantartas_${license_plate}_${year}_eves.${extension}`;
      }

      a.download = fileName;
      document.body.appendChild(a);
      a.click();

      // Tisztítás
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);

      // Bezárjuk a párbeszédablakot
      onOpenChange(false);
    } catch (err) {
      console.error("Exportálási hiba:", err);
      setError(
        err instanceof Error
          ? err.message
          : "Ismeretlen hiba történt az exportálás során."
      );
    } finally {
      setIsExporting(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Útnyilvántartás exportálása</DialogTitle>
          <DialogDescription>
            {year}. {monthName} havi útnyilvántartás exportálása.
          </DialogDescription>
        </DialogHeader>

        {cars.length === 0 ? (
          <Card className="mt-4 border-amber-200 bg-amber-50">
            <CardContent className="flex items-start gap-2">
              <AlertCircle className="h-4 w-4 text-amber-500 mt-0.5" />
              <div className="text-sm text-amber-700">
                Nincs elérhető jármű az exportáláshoz ebben a hónapban.
              </div>
            </CardContent>
          </Card>
        ) : (
          <>
            <div className="py-4">
              <div className="space-y-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium">
                    Válassza ki a járművet:
                  </label>
                  <Select
                    value={selectedCarId}
                    onValueChange={handleCarChange}
                    disabled={isExporting}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Válasszon járművet" />
                    </SelectTrigger>
                    <SelectContent>
                      {cars.map((car) => (
                        <SelectItem key={car.id} value={String(car.id)}>
                          {car.manufacturer} {car.model} ({car.license_plate})
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <Tabs
                  value={exportTab}
                  onValueChange={handleTabChange}
                  className="w-full"
                >
                  <TabsList className="grid grid-cols-2 w-full">
                    <TabsTrigger value="monthly">Havi kimutatás</TabsTrigger>
                    <TabsTrigger value="yearly">Éves kimutatás</TabsTrigger>
                  </TabsList>

                  <TabsContent value="monthly" className="space-y-4">
                    <div className="mt-4">
                      <div className="text-sm font-medium mb-2">
                        {year}. {monthName} havi útnyilvántartás
                      </div>

                      <div className="space-y-2">
                        <div className="text-sm font-medium">
                          Exportálás formátuma:
                        </div>
                        <div className="flex gap-2">
                          <Button
                            variant={
                              monthlyExportType === "word"
                                ? "default"
                                : "outline"
                            }
                            size="sm"
                            onClick={() => handleExportTypeChange("word")}
                            disabled={isExporting}
                            className="flex-1"
                          >
                            <FileText className="mr-2 h-4 w-4" />
                            Word (.docx)
                          </Button>
                          <Button
                            variant={
                              monthlyExportType === "excel"
                                ? "default"
                                : "outline"
                            }
                            size="sm"
                            onClick={() => handleExportTypeChange("excel")}
                            disabled={isExporting}
                            className="flex-1"
                          >
                            <FileSpreadsheet className="mr-2 h-4 w-4" />
                            Excel (.xlsx)
                          </Button>
                        </div>
                      </div>
                    </div>
                  </TabsContent>

                  <TabsContent value="yearly" className="space-y-4">
                    <div className="mt-4">
                      <div className="text-sm font-medium mb-2">
                        {year}. évi teljes útnyilvántartás
                      </div>

                      <Card className="bg-blue-50 border-blue-200">
                        <CardContent className="pt-4 flex items-start gap-2">
                          <FileSpreadsheet className="h-4 w-4 text-blue-500 mt-0.5" />
                          <div className="text-sm text-blue-700">
                            A teljes éves útnyilvántartás Excel (.xlsx)
                            formátumban exportálható.
                          </div>
                        </CardContent>
                      </Card>
                    </div>
                  </TabsContent>
                </Tabs>

                {error && (
                  <Card className="bg-red-50 border-red-200">
                    <CardContent className="flex items-start gap-2">
                      <AlertCircle className="h-4 w-4 text-red-500 mt-0.5" />
                      <div className="text-sm text-red-700">{error}</div>
                    </CardContent>
                  </Card>
                )}
              </div>
            </div>

            <DialogFooter>
              <Button
                variant="outline"
                onClick={() => onOpenChange(false)}
                disabled={isExporting}
              >
                Mégsem
              </Button>
              <Button
                onClick={handleExport}
                disabled={isExporting || !selectedCarId}
              >
                {isExporting ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Exportálás...
                  </>
                ) : (
                  <>
                    {exportTab === "monthly" && monthlyExportType === "word" ? (
                      <FileText className="mr-2 h-4 w-4" />
                    ) : (
                      <FileSpreadsheet className="mr-2 h-4 w-4" />
                    )}
                    Exportálás
                  </>
                )}
              </Button>
            </DialogFooter>
          </>
        )}
      </DialogContent>
    </Dialog>
  );
}

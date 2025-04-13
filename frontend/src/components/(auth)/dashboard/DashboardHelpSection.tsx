import { Info } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function DashboardHelpSection() {
  return (
    <Card className="mt-6">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-lg">
          <Info className="w-5 h-5 text-blue-500 dark:text-blue-400" />
          Irányítópult használata – Segítség
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-3 text-sm text-muted-foreground leading-relaxed">
        <p>
          Üdvözlünk az Irányítópulton! Ez a felület segítséget nyújt az
          alapfunkciók használatában.
        </p>
        <ul className="list-disc list-inside space-y-1">
          <li>
            <strong>Bal oldali menü</strong>: Navigálj az elérhető funkciók
            között, például adatlapok, nyilvántartások vagy beállítási
            lehetőségek.
          </li>
          <li>
            <strong>Adatkezelés</strong>: A jogosultságaidtól függően
            megtekintheted, szerkesztheted vagy új adatokat is rögzíthetsz.
          </li>
          <li>
            <strong>Szerepkörök és hozzáférés</strong>: A rendszerben minden
            felhasználó szerepkör szerint fér hozzá a funkciókhoz.
          </li>
          <li>
            <strong>Sötét/világos mód</strong>: A kezelőfelület automatikusan
            igazodik a böngésződ vagy eszközöd beállításaihoz.
          </li>
        </ul>
        <p>
          Kérdés, bármilyen elakadás vagy észrevétel esetén esetén fordulj a
          fejlesztő kollégákhoz, vagy az adminisztrátorhoz; végső esetben pedig
          írj az{" "}
          <span className="font-medium text-blue-500 dark:text-blue-400">
            info@a-ponton.hu
          </span>{" "}
          e-mail címre.
        </p>
      </CardContent>
    </Card>
  );
}

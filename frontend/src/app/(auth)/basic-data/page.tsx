"use client";

import Link from "next/link";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { FaListUl } from "react-icons/fa";
import { basicDataDashboardSections } from "@/lib/data/basic-data-dashboard";

export default function BasicDataDashboardPage() {
  return (
    <div className="container mx-auto py-10">
      <div className="flex items-center gap-3 mb-8">
        <FaListUl className="size-8 text-primary" />
        <h1 className="text-3xl font-bold">Adataim</h1>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {basicDataDashboardSections.map((section) => (
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
          Mire szolgálnak az „Adataim” oldalak?
        </h2>
        <div className="prose max-w-none leading-relaxed text-sm md:text-base">
          <p>
            Az „Adataim” szekció lehetőséget nyújt a cég működéséhez szorosan
            kapcsolódó alapinformációk strukturált kezelésére. Ezek az adatok
            elengedhetetlenek a pontos nyilvántartások, adminisztrációk és
            riportálások szempontjából.
          </p>

          <p>Ezen az oldalon a következő adatcsoportokat kezelheti:</p>

          <ul className="list-disc pl-4">
            <li>
              <strong>Székhelyek és telephelyek:</strong> A cég hivatalos címei,
              irodahelyszínei és működési pontjai. Itt megadhatja, melyik cím
              tekinthető székhelynek, és rögzítheti a telephelyeket is.
            </li>
            <li>
              <strong>Partnerek:</strong> Együttműködő szervezetek, intézmények,
              üzletek és egyéb partneri lokációk.
            </li>
            <li>
              <strong>Töltőállomások:</strong> Benzin- és elektromos
              töltőállomások listája, amelyekből az alkalmazottak tankolhatnak.
              Később útnyilvántartásban és költségelszámolásnál is használhatók.
            </li>
            <li>
              <strong>Autók:</strong> Céges vagy munkavállalókhoz tartozó
              járművek adatai, ideértve a rendszámot, típust, üzemanyagfajtát és
              autóvezetőt is.
            </li>
            <li>
              <strong>Utazás célja:</strong> Utazásokhoz megadható célok és azok
              típusai (üzleti, magán), amelyek automatikusan bekerülnek a
              munkanyilvántartásokba.
            </li>
            <li>
              <strong>NAV üzemanyagárak:</strong> A Nemzeti Adó- és Vámhivatal
              által közzétett havi üzemanyagárak nyilvántartása – ezek az
              elszámoláshoz és jelentésekhez is szükségesek.
            </li>
          </ul>

          <p className="mt-4">
            Az itt található információk naprakészen tartása biztosítja a
            rendszer pontos és hatékony működését, megkönnyítve a belső
            adminisztrációt, az elszámolásokat, valamint a jogszabályi
            megfelelést is.
          </p>
        </div>
      </div>
    </div>
  );
}

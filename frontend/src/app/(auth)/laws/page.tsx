"use client";

import Link from "next/link";
import { FaScaleBalanced } from "react-icons/fa6";

import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { lawCategories, textsForReading } from "@/lib/data/laws-dashboard";

export default function LawsDashboardPage() {
  return (
    <div className="container mx-auto py-10">
      <div className="flex items-center gap-3 mb-8">
        <FaScaleBalanced className="size-8 text-primary" />
        <h1 className="text-3xl font-bold">Jogszabályok</h1>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-12">
        {lawCategories.map((category) => (
          <Link href={category.href} key={category.title} className="group">
            <Card className="h-full transition-all duration-300 hover:shadow-md group-hover:translate-y-[-2px] overflow-hidden">
              <div
                className={`h-2 w-full ${category.color} transition-all duration-300 group-hover:h-3`}
              />
              <CardContent className="p-6">
                <div className="flex items-start gap-4 mb-3">
                  <div className={`rounded p-2 ${category.color}`}>
                    <category.icon className={`size-5 ${category.textColor}`} />
                  </div>
                  <div>
                    <h3 className="font-semibold text-lg">{category.title}</h3>
                    <p className="text-sm text-muted-foreground mt-1">
                      {category.description}
                    </p>
                  </div>
                </div>
                <div className="mt-4">
                  <Button
                    variant="outline"
                    className="w-full group-hover:bg-accent transition-colors cursor-pointer"
                  >
                    Megtekintés
                  </Button>
                </div>
              </CardContent>
            </Card>
          </Link>
        ))}
      </div>

      <div className="bg-muted/40 rounded-xl p-6 md:p-8">
        <h2 className="text-2xl font-semibold mb-6">Jogi információk</h2>

        <div className="grid md:grid-cols-3 gap-6 mb-6">
          {textsForReading.map((example, index) => (
            <div key={index} className="flex gap-3">
              <div className="mt-1">
                <div className="bg-primary/10 rounded-full p-2">
                  <example.icon className="size-5 text-primary" />
                </div>
              </div>
              <div>
                <h3 className="font-medium text-base mb-1">{example.title}</h3>
                <p className="text-sm text-muted-foreground">
                  {example.content}
                </p>
              </div>
            </div>
          ))}
        </div>

        <div className="prose prose-sm max-w-none mt-8">
          <p>
            A jogszabályi tár célja, hogy a földmérési és
            ingatlan-nyilvántartási tevékenységekhez kapcsolódó jogszabályok
            könnyen elérhetőek legyenek. Fontos tudni, hogy a jogszabályok
            változhatnak, és azokat a gyakorlatban mindig az aktuális hivatalos
            változatukban kell alkalmazni.
          </p>
          <p>
            A jogszabályok értelmezéséhez mindig érdemes jogi szakember
            véleményét is kikérni, különösen bonyolultabb esetekben. Cégünk
            rendszeresen frissíti a jogszabályi adatbázist, hogy az a lehető
            legpontosabb információkat nyújtsa, de a hivatalos állami
            jogszabálytár (
            <a
              href="https://njt.hu/"
              target="_blank"
              rel="noopener noreferrer"
              className="text-primary underline hover:text-primary/80 transition-colors"
            >
              njt.hu
            </a>
            ) mindig elsőbbséget élvez.
          </p>
          <p>
            A tárolt dokumentumok nem helyettesítik a hivatalos, közhiteles
            nyilvántartásokat és jogszabályi szövegeket, azokat elsősorban
            tájékoztatási céllal tesszük közzé.
          </p>
        </div>
      </div>
    </div>
  );
}

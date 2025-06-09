import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

export function FeesIntro() {
  return (
    <div className="w-full max-w-6xl mx-auto space-y-6">
      <div className="mb-6">
        <strong>Használt rövidítések: </strong>
        hrsz: helyrajziszám (ingatlan, EÖI), frsz: földrészlet (telek)
      </div>
      <div className="bg-muted/50 p-4 rounded-lg">
        <h2 className="text-lg font-semibold mb-2">
          Általános ingatlan-nyilvántartási eljárási díj ingatlanonként:
        </h2>
        <p className="text-2xl font-bold text-primary">10.600 Ft</p>
      </div>

      <Accordion type="single" collapsible className="w-full">
        <AccordionItem value="land-survey-services">
          <AccordionTrigger className="text-xl font-semibold">
            Földmérési adatszolgáltatás
          </AccordionTrigger>
          <AccordionContent>
            <div className="grid md:grid-cols-2 gap-6">
              <div>
                <h3 className="font-semibold mb-2">
                  Földrészletek határvonalát érintő munkához:
                </h3>
                <ul className="list-disc pl-5 space-y-1">
                  <li>10 ha-ig: 10.000 Ft</li>
                  <li>10–50 ha: 16.000 Ft</li>
                  <li>50–100 ha: 19.000 Ft</li>
                  <li>
                    100 ha felett: fel kell szorozni a ha/100 résszel három
                    tizedes élességgel
                    <span className="block text-sm text-muted-foreground">
                      (Pl. 125 ha 2112 m<sup>2</sup> esetében: 19.000 × 1,252 =
                      23.788 Ft)
                    </span>
                  </li>
                </ul>
                <p className="mt-4 text-justify">
                  Egynél több, egymás mellett elhelyezkedő földrészlet esetén az
                  adatok díját a legnagyobb földrészletre 100%-kal, minden
                  további földrészletre 50%-kal, telekegyesítés esetében pedig a
                  minden további földrészletre 20%-kal kell figyelembe venni.
                </p>
              </div>

              <div>
                <h3 className="font-semibold mb-2">
                  Földrészletek határvonalát nem érintő munkához:
                </h3>
                <ul className="list-disc pl-5 space-y-1">
                  <li>10 ha-ig: 6.500 Ft</li>
                  <li>10–50 ha: 13.000 Ft</li>
                  <li>50–100 ha: 14.500 Ft</li>
                  <li>
                    100 ha felett: fel kell szorozni a ha/100 résszel három
                    tizedes élességgel
                    <span className="block text-sm text-muted-foreground">
                      (Pl. 125 ha 2112 m<sup>2</sup> esetében: 14.500 × 1,252 =
                      18.154 Ft)
                    </span>
                  </li>
                </ul>
                <p className="mt-4 text-justify">
                  Jog vagy tény bejegyzéséhez és termőföld más célú
                  hasznosításával kapcsolatos művelési ág változási vázrajz
                  készítéséhez az adatok díját a legnagyobb földrészletre
                  100%-kal, minden további földrészletre 20%-kal kell figyelembe
                  venni.
                </p>
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="inspection-fees">
          <AccordionTrigger className="text-xl font-semibold">
            Vizsgálati díjak
          </AccordionTrigger>
          <AccordionContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-4">
                <div>
                  <h3 className="font-semibold mb-2">
                    Telekegyesítés{" "}
                    <span className="font-normal">
                      (a földrészletek számától függetlenül): 4.000 Ft
                    </span>
                  </h3>
                  <p></p>
                </div>

                <div>
                  <h3 className="font-semibold mb-2">
                    Telekmegosztás, telekcsoport-újraosztás, telekhatárrendezés:
                  </h3>
                  <ul className="list-disc pl-5 space-y-1">
                    <li>2-5 db frsz: 4.000 Ft</li>
                    <li>6-10 db frsz: 5.600 Ft</li>
                    <li>11-30 db frsz: 9.600 Ft</li>
                    <li>31-50 db frsz: 12.000 Ft</li>
                    <li>51-100 db frsz: 17.600 Ft</li>
                    <li>100 db frsz-től: 17.600 Ft + 160 Ft/frsz</li>
                  </ul>
                </div>

                <div>
                  <h3 className="mb-2">
                    <span className="font-semibold">Használati megosztás</span>i
                    vázrajz vizsgálati díja: 3.200 Ft
                  </h3>
                  <p></p>
                </div>

                <div>
                  <h3 className="mb-2">
                    <span className="font-semibold">
                      Földrészlethatár-kitűzés
                    </span>
                    i vázrajz vizsgálati díja: 3.200 Ft
                  </h3>
                  <p></p>
                </div>

                <div>
                  <h3 className="mb-2">
                    <span className="font-semibold">
                      Közigazgatási és fekvéshatárok módosítása
                    </span>{" "}
                    a földrészletek számától függetlenül: 4.000 Ft
                  </h3>
                  <p></p>
                </div>
              </div>

              <div className="space-y-4">
                <div>
                  <h3 className="mb-2">
                    <span className="font-semibold">Építmények</span>kel
                    kapcsolatos változási vázrajzok vizsgálati díja: 10.600 Ft
                  </h3>
                  <p></p>
                  <p className="text-sm mt-1">
                    Továbbá minden új, változott vagy megszűnt építményenként:
                    2.000 Ft
                  </p>
                  <p className="text-sm text-muted-foreground mt-1">
                    Egy földrészleten belül egy megszüntetett és egy új építmény
                    esetében az építményenként számítandó vizsgálati díjat csak
                    egy építmény után kell megfizetni.
                  </p>
                </div>

                <div>
                  <h3 className="mb-2">
                    <span className="font-semibold">
                      {" "}
                      Társasházak és szövetkezeti házak alaprajz
                    </span>
                    ának vizsgálata kialakuló vagy módosított egyéb önálló
                    ingatlanonként: 2.000 Ft
                  </h3>
                  <p></p>
                </div>

                <div>
                  <h3 className="mb-2">
                    <span className="font-semibold">Művelési ág változás</span>
                    ával kapcsolatos változási vázrajz vizsgálata:
                  </h3>
                  <ul className="list-disc pl-5 space-y-1">
                    <li>1-5 alrészletig: 1.000 Ft/új alrészlet</li>
                    <li>További új alrészletek: 200 Ft/új alrészlet</li>
                  </ul>
                </div>

                <div>
                  <h3 className=" mb-2">
                    <span className="font-semibold">
                      Jog vagy tény bejegyzéséhez
                    </span>{" "}
                    változási vázrajz vizsgálata: 1.500 Ft
                  </h3>
                  <p></p>
                  <p className="text-sm mt-1">
                    Egymással összefüggő (több telket érintő) jog/tény esetén:
                    500 Ft/további frsz
                  </p>
                </div>
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="urgent-inspection-fees">
          <AccordionTrigger className="text-xl font-semibold">
            Soron kívüli vizsgálat
          </AccordionTrigger>
          <AccordionContent>
            <div className="grid grid-cols-1 md:grid-cols-2 auto-rows-min items-start gap-2">
              <div>
                <h3 className="font-semibold mb-2">Díjtételek</h3>
                <ul className="list-disc pl-5 space-y-1">
                  <li className="text-justify mt-1">
                    1-5 db hrsz: 16.000 Ft/hrsz
                  </li>
                  <li className="text-justify mt-1">
                    6-15 db hrsz: 80.000 Ft + a 6. hrsztől 8.000 Ft/hrsz
                  </li>
                  <li className="text-justify mt-1">
                    16 db hrsz felett: 160.000 Ft + a 16. hrsztől 800 Ft/hrsz
                  </li>
                </ul>
              </div>
              <div>
                <h3 className="font-semibold mb-2">
                  Érintett ingatlanok száma:
                </h3>
                <ul className="list-disc pl-5 space-y-1">
                  <li>
                    társasház vagy szövetkezeti ház esetében az érintett (új,
                    változott) EÖI-k száma
                  </li>
                  <li>
                    telekegyesítés, telekhatár-rendezés esetén a kiinduló
                    állapot szerinti földrészletek száma
                  </li>
                  <li>
                    telekfelosztás, telekcsoport újraosztása esetén a kialakuló
                    földrészletek száma
                  </li>
                  <li>
                    minden más esetben a kérelem benyújtásakor önálló
                    földrészletként nyilvántartott ingatlanok száma
                  </li>
                </ul>
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="land-modification-approval">
          <AccordionTrigger className="text-xl font-semibold">
            Telekalakítás engedélyezési eljárás
          </AccordionTrigger>
          <AccordionContent>
            <div className="grid md:grid-cols-2 gap-4">
              <div>
                <ul className="list-disc pl-5 space-y-1">
                  <li className="text-justify">2-5 db frsz: 19.000 Ft/frsz</li>
                  <li className="text-justify">
                    6-15 db frsz: 96.000 Ft + a 6. frsz-től 16.000 Ft/frsz
                  </li>
                  <li className="text-justify">
                    16-25 db frsz: 256.000 Ft + a 16. frsz-től 1.600 Ft/frsz
                  </li>
                  <li className="text-justify">
                    26 db frsz felett: 272.000 Ft + a 26. frsz-től 800 Ft/frsz
                  </li>
                </ul>
              </div>
              <div>
                <h3 className="font-semibold mb-2">
                  Érintett földrészletek száma:
                </h3>
                <p className="text-justify mt-1">
                  a) telekegyesítés, telekhatárendezés esetén a változás előtti
                  állapot szerint
                </p>
                <p className="text-justify mt-1">
                  b) telekfelosztás, telekcsoport újraosztás esetén a változás
                  utáni földrészletek száma
                </p>
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>

        <AccordionItem value="document-fees">
          <AccordionTrigger className="text-xl font-semibold">
            Tulajdoni lap, térképmásolat, iratmásolat
          </AccordionTrigger>
          <AccordionContent>
            <div className="grid md:grid-cols-2 gap-6">
              <div>
                <ul className="list-disc pl-5 space-y-1">
                  <li className="text-justify">
                    Papír alapú tulajdoni lap másolat: 10.000 Ft/hrsz
                  </li>
                  <li className="text-justify">
                    Elektronikus tulajdoni lap másolat: 4.800 Ft/hrsz
                  </li>
                  <li className="text-justify">Térképmásolat: 5.000 Ft/frsz</li>
                  <li className="text-justify">
                    Archív munkarészek másolata: 320 Ft/A4 lap
                  </li>
                  <li className="text-justify">Iratmásolat: 160 Ft/A4 lap</li>
                </ul>
              </div>
            </div>
          </AccordionContent>
        </AccordionItem>
      </Accordion>
    </div>
  );
}

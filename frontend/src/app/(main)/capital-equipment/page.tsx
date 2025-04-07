import EquipmentCard from "@/components/(main)/capital-equipment/EquipmentCard";
import EquipmentList from "@/components/(main)/capital-equipment/EquipmentList";
import { equipmentData } from "@/lib/data/capital-equipment-page-data";
import { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Munkaeszközeink",
};

export default function CapitalEquipmentPage() {
  return (
    <div className="pt-19 bg-gray-200 dark:bg-zinc-400/60">
      <div className="full-width-container mx-auto px-8 md:px-16">
        <h1 className="text-center lg:text-left text-2xl sm:text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold my-6">
          Eszközparkunk
        </h1>
        <p className="text-justify md:text-lg lg:text-xl 2xl:text-2xl mb-8">
          <b>
            Cégünk eszközállományát főbb tevékenységeinek megfelelően alakította
            ki és fejleszti folyamatosan
          </b>
          . Szerteágazó szolgáltatásainknak köszönhetően{" "}
          <b>eszköztárunkban egyaránt helyet kapnak a régi jól bevált</b>,{" "}
          nagypontosságú{" "}
          <b>mérőállomások és a legmodernebb robot-mérőállomások</b>.
        </p>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 justify-items-center">
          <EquipmentCard {...equipmentData[0]} />
          <div className="flex items-center justify-center text-justify p-4 xl:bg-gray-100 rounded-md h-full overflow-y-auto max-w-xl">
            <p className="text-sm md:text-base lg:text-lg leading-relaxed">
              Rendkívül gyors (ezáltal költséghatékony){" "}
              <b>
                3D-s lézerszkennerünkkel részletes, pontos geometriai és képi
                információkat rögzíthetünk épületekről
              </b>
              , műemlékekről. A{" "}
              <span lang="en">
                <b>Trimble</b> RealWorks
              </span>{" "}
              és a <span lang="en">Trimble Perspective</span>{" "}
              <b>
                szoftvereink segítségével végezzük a szkennerrel készült
                pontfelhők feldolgozását
              </b>
              .<br />
              <b>A világ első öntanuló mérőállomásának működtetésével</b>{" "}
              bármilyen terepen - miképpen a műszer a környezeti feltételekhez
              automatikusan igazodik -{" "}
              <b>pontosan rögzíthetjük a környezetet</b>.{" "}
              <b>Az ehhez tartozó</b>{" "}
              <span lang="en">
                <b>Leica</b> Captivate
              </span>{" "}
              <b>
                szoftver egyetlen húzással kapcsolja össze a különféle
                szakterületek alkalmazásait és legbonyolultabb műveleteit
              </b>
              , továbbá a mért és a tervezett adatokat minden dimenzióban
              megtekinthetővé teszi számunkra.
            </p>
          </div>
          <EquipmentCard {...equipmentData[1]} />
        </div>
        <EquipmentList start={2} end={6} />
        <EquipmentCard {...equipmentData[6]} />
        <div className="text-center max-w-3xl mx-auto my-8">
          <p>
            A <b>hatékony irodai munkánk elengedhetetlen kelléke</b>i a
            különböző irodatechnikai berendezések. Ezek közül a legjelentősebb{" "}
            <b>a</b>{" "}
            <span lang="en">
              <b>HP DesignJet</b>
            </span>{" "}
            <b>T520-as plotterünk, valamint hordozható</b>{" "}
            <span lang="en">
              <b>HP OfficeJet 200</b>
            </span>{" "}
            <b>mobil nyomtatónk</b>.<br />
            Eszközeinket és gépkocsiparkunkat dolgozóink igényeinek
            figyelembevételével folyamatosan fejlesztjük.
          </p>
        </div>
        <EquipmentCard {...equipmentData[7]} />
        <div className="text-center mt-8">
          <Link href="/documents/epuletfelmeres.pdf" target="_blank">
            Tudj meg többet a lézerszkennelésről: Épület teljes felmérése 3D
            lézerszkenneléssel
          </Link>
        </div>
      </div>
    </div>
  );
}

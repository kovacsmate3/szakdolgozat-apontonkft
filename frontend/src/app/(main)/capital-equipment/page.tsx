import FeaturedEquipment from "@/components/(main)/capital-equipment/featured/FeaturedEquipment";
import LearnMoreSection from "@/components/(main)/capital-equipment/LearnMoreSection";
import PrinterEquipment from "@/components/(main)/capital-equipment/printers/PrinterEquipment";
import SecondaryEquipment from "@/components/(main)/capital-equipment/secondary/SecondaryEquipment";
import { equipmentData } from "@/lib/data/capital-equipment-page-data";
import { Metadata } from "next";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Munkaeszközeink",
};

export default function CapitalEquipmentPage() {
  // Főműszerek
  const featuredItems: [(typeof equipmentData)[0], (typeof equipmentData)[1]] =
    [equipmentData[0], equipmentData[1]];

  // Másodlagos műszerek
  const secondaryItems = equipmentData.slice(2, 6);

  // Nyomtatók
  const printerItems: [(typeof equipmentData)[6], (typeof equipmentData)[7]] = [
    equipmentData[6],
    equipmentData[7],
  ];

  return (
    <div className="bg-[url('/images/(main)/capital-equipment/fohatter3.jpeg')] bg-cover bg-center min-h-screen pt-20">
      <div className="full-width-container mx-auto px-8 md:px-16 pb-12">
        <h1 className="text-center lg:text-left text-2xl sm:text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold my-6 text-white">
          Eszközparkunk
        </h1>

        <p className="text-justify md:text-lg lg:text-xl 2xl:text-2xl mb-10 text-white px-2">
          <b>
            Cégünk eszközállományát főbb tevékenységeinek megfelelően alakította
            ki és fejleszti folyamatosan
          </b>
          . Szerteágazó szolgáltatásainknak köszönhetően{" "}
          <b>eszköztárunkban egyaránt helyet kapnak a régi jól bevált</b>,{" "}
          nagypontosságú{" "}
          <b>mérőállomások és a legmodernebb robot-mérőállomások</b>.
        </p>

        <section className="mb-12">
          <FeaturedEquipment items={featuredItems} />
        </section>

        <section className="mb-12">
          <SecondaryEquipment items={secondaryItems} />
        </section>

        <section className="mb-12">
          <PrinterEquipment items={printerItems} />
        </section>

        <LearnMoreSection />
      </div>
    </div>
  );
}

import MoreSection from "@/components/(main)/references/MoreSection";
import ReferenceCard from "@/components/(main)/references/ReferenceCard";
import { references } from "@/lib/data/reference-page-data";
import { Metadata } from "next";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Referenciáink",
};

export default function ReferencesPage() {
  const budapestiReferences = references.filter(
    (ref) => ref.location === "Budapesti"
  );
  const videkiReferences = references.filter(
    (ref) => ref.location === "Vidéki"
  );

  return (
    <div className="pt-19 bg-gray-200 dark:bg-zinc-400/60">
      <div className="full-width-container mx-auto px-8 md:px-16">
        <h1 className="text-center lg:text-left text-2xl sm:text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold my-6">
          Projektjeink
        </h1>
        <p className="text-justify lg:text-left text-lg md:text-xl mb-8">
          <b>
            Vállalatunk törekszik a megrendelők igénye szerint, a szakmai
            szabványok és szabályzatok előírásainak maradéktalan betartása
            mellett a minőség garantálására
          </b>
          , amelyet korábbi, illetve jelenleg is folyamatban lévő projektjeink
          is alátámasztanak.
        </p>
        <h2 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl 2xl:text-5xl font-semibold mb-4">
          Budapesti megbízatások
        </h2>
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-8 pb-6">
          {budapestiReferences.map((ref, index) => (
            <ReferenceCard key={index} {...ref} />
          ))}
        </div>
        <h2 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl 2xl:text-5xl font-semibold mb-4">
          Vidéki munkák
        </h2>
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-8 pb-6">
          {videkiReferences.map((ref, index) => (
            <ReferenceCard key={index} {...ref} />
          ))}
        </div>
        <MoreSection />
      </div>
    </div>
  );
}

"use client";

import { FeesIntro } from "@/components/(auth)/laws/fees/FeesIntro";
import { LawCard } from "@/components/(auth)/laws/LawCard";
import { Skeleton } from "@/components/ui/skeleton";
import { Law } from "@/lib/types";
import { getFeesLaws } from "@/server/laws";
import { useQuery } from "@tanstack/react-query";

interface Props {
  token: string;
}

export default function FeesPageClient({ token }: Props) {
  const {
    data: laws,
    isLoading,
    error,
  } = useQuery({
    queryKey: ["laws", "land-measurement"],
    queryFn: () => getFeesLaws(token),
  });

  if (isLoading) {
    return (
      <div className="container mx-auto py-10">
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-2xl font-semibold">Eljárási díjak</h1>
        </div>
        {/* Skeletons: 4 cards per row */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {[...Array(4)].map((_, index) => (
            <Skeleton key={index} className="h-40 w-full" />
          ))}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto py-10">
        <h1 className="text-2xl font-semibold mb-6">Hiba történt</h1>
        <p>Nem sikerült betölteni a jogszabályokat.</p>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-10">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-semibold">Eljárási díjak</h1>
      </div>
      {/* itt indítjuk a közös gridet */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* FeesIntro full‑width, de ugyanattól a bal oldali grid‑vonalnál kezd */}
        <div className="col-span-full">
          <FeesIntro />
        </div>

        {/* és utána jönnek a kártyák */}
        {laws?.map((law: Law) => <LawCard key={law.id} law={law} />)}
      </div>
    </div>
  );
}

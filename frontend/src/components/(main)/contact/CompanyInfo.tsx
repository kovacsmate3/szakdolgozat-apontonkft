"use client";

import { MapProvider } from "@/providers/map-provider";
import dynamic from "next/dynamic";

const MapWithAdvancedMarkers = dynamic(
  () => import("./MapWithAdvancedMarkers"),
  {
    ssr: false,
  }
);

const CompanyInfo = () => {
  return (
    <div className="flex flex-col lg:flex-row items-center mx-4 mb-8">
      <div className="w-full lg:w-1/2 text-center p-3">
        <h2 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-semibold mb-2">
          A cég székhelye:
        </h2>
        <h3 className="text-lg sm:text-xl md:text-2xl lg:text-3xl font-medium">
          1151 Budapest, Esthajnal utca 3.
        </h3>
      </div>
      <div className="w-full lg:w-1/2 flex justify-center items-center p-3 h-[450px]">
        <MapProvider>
          <MapWithAdvancedMarkers />
        </MapProvider>
      </div>
    </div>
  );
};

export default CompanyInfo;

"use client";

import { useEffect, useRef } from "react";

const MapWithAdvancedMarkers = () => {
  const mapRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    const initMap = async () => {
      const google = window.google;
      if (!google?.maps) return;

      const { Map } = (await google.maps.importLibrary(
        "maps"
      )) as google.maps.MapsLibrary;
      const { AdvancedMarkerElement, PinElement } =
        (await google.maps.importLibrary(
          "marker"
        )) as google.maps.MarkerLibrary;

      const map = new Map(mapRef.current as HTMLElement, {
        center: { lat: 47.5723935, lng: 19.1267245 },
        zoom: 13,
        mapId: "DEMO_MAP_ID",
      });

      const hqPin = new PinElement({
        glyph: "🏢",
        background: "#000000",
        borderColor: "#333333",
        glyphColor: "#FFFFFF",
      });

      new AdvancedMarkerElement({
        map,
        position: { lat: 47.572636, lng: 19.1255497 },
        content: hqPin.element,
        title: "Székhely – Esthajnal utca 3.",
      });

      const officeLocations = [
        {
          position: { lat: 47.5599203, lng: 19.0813111 },
          title: "Iroda – Lorántffy Zsuzsanna utca 8.",
        },
        {
          position: { lat: 47.5833925, lng: 19.1204172 },
          title: "Iroda – Székely Elek út 11.",
        },
      ];

      officeLocations.forEach((loc) => {
        const pin = new PinElement({
          glyph: "💼",
          background: "#E5E7EB",
          borderColor: "#9CA3AF",
          glyphColor: "#111827",
        });

        new AdvancedMarkerElement({
          map,
          position: loc.position,
          content: pin.element,
          title: loc.title,
        });
      });
    };

    if (typeof window !== "undefined") {
      initMap();
    }
  }, []);

  return <div ref={mapRef} className="w-full h-[450px] rounded-md shadow-md" />;
};

export default MapWithAdvancedMarkers;

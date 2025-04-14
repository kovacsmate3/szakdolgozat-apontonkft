import RoutePlannerMap from "@/components/(auth)/road-record/route-planning/RoutePlannerMap";
import { MapProvider } from "@/providers/map-provider";

export default function RoutePlanningPage() {
  return (
    <MapProvider>
      <main>
        <RoutePlannerMap />
      </main>
    </MapProvider>
  );
}

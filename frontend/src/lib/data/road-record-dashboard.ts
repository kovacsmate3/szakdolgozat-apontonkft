import { BiTrip } from "react-icons/bi";
import { FaGasPump } from "react-icons/fa";
import { Map } from "lucide-react";

export const roadRecordSections = [
  {
    title: "Havi utak",
    description:
      "Rögzített utazások kezelése, naptári nézetben való böngészése és exportálása",
    href: "/road-record/monthly-trips",
    icon: BiTrip,
  },
  {
    title: "Tankolások/Töltések",
    description: "Üzemanyag költségek és tankolások nyilvántartása, elemzése",
    href: "/road-record/refueling",
    icon: FaGasPump,
  },
  {
    title: "Útvonaltervezés",
    description: "Útvonalak tervezése, távolság és költségkalkuláció",
    href: "/road-record/route-planning",
    icon: Map,
  },
];

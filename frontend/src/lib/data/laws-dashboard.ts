import {
  LandPlot,
  BrickWall,
  HandCoins,
  Ellipsis,
  BookOpenCheck,
  RefreshCw,
  Info,
} from "lucide-react";
import { MdRealEstateAgent } from "react-icons/md";
import { PiMapPinSimpleArea } from "react-icons/pi";

export const lawCategories = [
  {
    title: "Földmérés",
    description: "Földmérési és térképészeti tevékenységről szóló jogszabályok",
    icon: LandPlot,
    href: "/laws/land-measurement",
    color: "bg-emerald-100 dark:bg-emerald-950",
    textColor: "text-emerald-700 dark:text-emerald-300",
  },
  {
    title: "Ingatlan-nyilvántartás",
    description: "Az ingatlanok jogi nyilvántartását szabályozó törvények",
    icon: MdRealEstateAgent,
    href: "/laws/property",
    color: "bg-blue-100 dark:bg-blue-950",
    textColor: "text-blue-700 dark:text-blue-300",
  },
  {
    title: "Építésügy",
    description: "Építésügyi hatósági eljárások és engedélyezési folyamatok",
    icon: BrickWall,
    href: "/laws/construction",
    color: "bg-amber-100 dark:bg-amber-950",
    textColor: "text-amber-700 dark:text-amber-300",
  },
  {
    title: "Földügy",
    description:
      "Földhasználat, földvédelem, földhasznosítás jogszabályi háttere",
    icon: PiMapPinSimpleArea,
    href: "/laws/land-affairs",
    color: "bg-indigo-100 dark:bg-indigo-950",
    textColor: "text-indigo-700 dark:text-indigo-300",
  },
  {
    title: "Eljárási díjak",
    description: "Földhivatali és hatósági eljárások díjtételei, illetékek",
    icon: HandCoins,
    href: "/laws/fees",
    color: "bg-rose-100 dark:bg-rose-950",
    textColor: "text-rose-700 dark:text-rose-300",
  },
  {
    title: "További jogszabályok",
    description: "Egyéb, kapcsolódó rendelkezések és jogszabályi források",
    icon: Ellipsis,
    href: "/laws/other",
    color: "bg-slate-100 dark:bg-slate-800",
    textColor: "text-slate-700 dark:text-slate-300",
  },
];

export const textsForReading = [
  {
    title: "Szakmai útmutató",
    content:
      "A tárolt jogszabályok segítenek a mindennapi szakmai munkában – ezek adják a földmérési és ingatlan-nyilvántartási tevékenységek alapját és kereteit.",
    icon: BookOpenCheck, // szakmai háttérre utaló könyv ikon pipa jellel
  },
  {
    title: "Naprakész információk",
    content:
      "Tárunk rendszeresen frissül, hogy mindig a legaktuálisabb jogi előírásoknak megfelelően dolgozhass. Időszakosan ellenőrizd a frissítéseket.",
    icon: RefreshCw, // frissítés ikon
  },
  {
    title: "Fontos tudnivalók",
    content:
      "A jogszabályok ismerete alapvető fontosságú a földmérői szakma gyakorlása során, minden esetben az aktuálisan hatályos rendelkezések az irányadóak.",
    icon: Info, // klasszikus információs ikon
  },
];

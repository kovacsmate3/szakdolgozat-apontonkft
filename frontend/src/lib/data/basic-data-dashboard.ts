import { FaBook } from "react-icons/fa";
import { FaCar } from "react-icons/fa6";
import { GrGroup } from "react-icons/gr";
import { TbGasStation } from "react-icons/tb";
import { IoMdPricetags } from "react-icons/io";
import { Building } from "lucide-react";

export const basicDataDashboardSections = [
  {
    title: "Székhelyek és telephelyek",
    description: "Cégünk telephelyeinek és székhelyeinek kezelése",
    icon: Building,
    href: "/basic-data/sites",
  },
  {
    title: "Partnerek",
    description: "Partnerek, egyéb és bolt típusú helyszínek",
    icon: GrGroup,
    href: "/basic-data/partners",
  },
  {
    title: "Töltőállomások",
    description: "Üzemanyagtöltő állomások nyilvántartása",
    icon: TbGasStation,
    href: "/basic-data/stations",
  },

  {
    title: "Autók",
    description: "Cég és magángépjárművek nyilvántartása",
    icon: FaCar,
    href: "/basic-data/cars",
  },
  {
    title: "Utazás célja",
    description: "Utazási célok szótára és kategóriái",
    icon: FaBook,
    href: "/basic-data/travel-reasons",
  },
  {
    title: "NAV Üzemanyagárak",
    description: "Hivatalos üzemanyagárak nyilvántartása",
    icon: IoMdPricetags,
    href: "/basic-data/fuel-prices",
  },
];

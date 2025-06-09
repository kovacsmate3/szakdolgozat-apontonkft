import {
  Building,
  Map,
  LandPlot,
  BrickWall,
  HandCoins,
  Ellipsis,
  Users,
  //CalendarCheck2,
  //FolderOpenDot,
} from "lucide-react";
import {
  //FaBusinessTime,
  FaGasPump,
  FaListUl,
  FaRoad,
  //FaTasks,
  FaBook,
} from "react-icons/fa";
import { VscGraph } from "react-icons/vsc";
import { GrGroup } from "react-icons/gr";
import { BiTrip } from "react-icons/bi";
import { IoMdPricetags } from "react-icons/io";
import { TbGasStation /*TbLockAccess*/ } from "react-icons/tb";
import { FaCar, FaUserShield, FaScaleBalanced } from "react-icons/fa6";
import { /*MdEditOff,*/ MdRealEstateAgent } from "react-icons/md";
import { PiMapPinSimpleArea } from "react-icons/pi";

export const navMain = [
  {
    title: "Irányítópult",
    url: "/dashboard",
    icon: VscGraph,
    isActive: true,
  },
  /*
  {
    title: "Munkanyilvántartás",
    url: "/timesheet",
    icon: FaBusinessTime,
    items: [
      {
        title: "Munkanapló",
        url: "/timesheet/daily-log",
        icon: CalendarCheck2,
      },
      {
        title: "Projektek (munkajegyzék)",
        url: "/timesheet/projects",
        icon: FolderOpenDot,
      },
      {
        title: "Feladatok",
        url: "/timesheet/tasks",
        icon: FaTasks,
      },
    ],
  },
  */
  {
    title: "Útnyilvántartás",
    url: "/road-record",
    icon: FaRoad,
    items: [
      {
        title: "Havi utak",
        url: "/road-record/monthly-trips",
        icon: BiTrip,
      },
      {
        title: "Tankolások/Töltések",
        url: "/road-record/refueling",
        icon: FaGasPump,
      },
      {
        title: "Útvonaltervezés",
        url: "/road-record/route-planning",
        icon: Map,
      },
    ],
  },
  {
    title: "Adataim",
    url: "/basic-data",
    icon: FaListUl,
    items: [
      {
        title: "Székhely/Telephelyek",
        url: "/basic-data/sites",
        icon: Building,
      },
      {
        title: "Partnerek",
        url: "/basic-data/partners",
        icon: GrGroup,
      },
      {
        title: "Töltőállomások",
        url: "/basic-data/stations",
        icon: TbGasStation,
      },
      {
        title: "Autók",
        url: "/basic-data/cars",
        icon: FaCar,
      },
      {
        title: "Utazás célja szótár",
        url: "/basic-data/travel-purposes",
        icon: FaBook,
      },
      {
        title: "NAV üzemanyagárak",
        url: "/basic-data/fuel-prices",
        icon: IoMdPricetags,
      },
    ],
  },
  {
    title: "Jogszabályok",
    url: "/laws",
    icon: FaScaleBalanced,
    items: [
      {
        title: "Földmérés",
        url: "/laws/land-measurement",
        icon: LandPlot,
      },
      {
        title: "Ingatlan-nyilvántartás",
        url: "/laws/property",
        icon: MdRealEstateAgent,
      },
      {
        title: "Építésügy",
        url: "/laws/construction",
        icon: BrickWall,
      },
      {
        title: "Földügy",
        url: "/laws/land-affairs",
        icon: PiMapPinSimpleArea,
      },
      {
        title: "Eljárási díjak",
        url: "/laws/fees",
        icon: HandCoins,
      },
      {
        title: "További jogszabályok",
        url: "/laws/other",
        icon: Ellipsis,
      },
    ],
  },
  {
    title: "Adminisztráció",
    url: "/admin",
    icon: FaUserShield,
    items: [
      { title: "Felhasználók", url: "/admin/users", icon: Users },
      //{ title: "Szerepkörök", url: "/admin/roles", icon: TbLockAccess },

      /*{
        title: "Jogosultságok",
        url: "/admin/permissions",
        icon: MdEditOff,
      },
      */
    ],
  },
];

export const navMain = [
  {
    title: "Kezdőlap",
    url: "/dashboard",
    isActive: true,
  },
  {
    title: "Munkanyilvántartás",
    url: "/timesheet",
    items: [
      {
        title: "Munkanapló",
        url: "/timesheet/daily-log",
      },
      {
        title: "Projektek (munkajegyzék)",
        url: "/timesheet/projects",
      },
      {
        title: "Feladatok",
        url: "/timesheet/tasks",
      },
    ],
  },
  {
    title: "Útnyilvántartás",
    url: "/road-record",
    items: [
      {
        title: "Havi utak",
        url: "/road-record/monthly-trips",
      },
      {
        title: "Tankolások / Töltések",
        url: "/road-record/refueling",
      },
      {
        title: "Útvonaltervezés",
        url: "/road-record/route-planning",
      },
    ],
  },
  {
    title: "Adataim",
    url: "/basic-data",
    items: [
      {
        title: "Székhely/telephelyek",
        url: "/basic-data/sites",
      },
      {
        title: "Partnerek",
        url: "/basic-data/partners",
      },
      {
        title: "Töltőállomások",
        url: "/basic-data/stations",
      },
      {
        title: "Autók",
        url: "/basic-data/cars",
      },
      {
        title: "Utazás célja szótár",
        url: "/basic-data/travel-reasons",
      },
      {
        title: "NAV üzemanyagárak",
        url: "/basic-data/fuel-prices",
      },
    ],
  },
  {
    title: "Jogszabályok gyűjteménye",
    url: "/laws",
    items: [
      {
        title: "Földmérés",
        url: "/laws/land-measurement",
      },
      {
        title: "Ingatlan-nyilvántartás",
        url: "/laws/property",
      },
      {
        title: "Építésügy",
        url: "/laws/construction",
      },
      {
        title: "Földügy",
        url: "/laws/land-affairs",
      },
      {
        title: "Eljárási díjak",
        url: "/laws/fees",
      },
      {
        title: "További jogszabályok",
        url: "/laws/other",
      },
    ],
  },
  {
    title: "Adminisztráció",
    url: "/admin",
    items: [
      { title: "Felhasználók", url: "/admin/users" },
      { title: "Szerepkörök", url: "/admin/roles" },
      {
        title: "Jogosultságok",
        url: "/admin/permissions",
      },
    ],
  },
];

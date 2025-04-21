import * as React from "react";
import {
  Minus,
  Plus,
  Building,
  Map,
  LandPlot,
  BrickWall,
  HandCoins,
  Ellipsis,
  Users,
  CalendarCheck2,
} from "lucide-react";
import {
  FaBusinessTime,
  FaGasPump,
  FaListUl,
  FaRoad,
  FaTasks,
  FaBook,
} from "react-icons/fa";
import { VscGraph } from "react-icons/vsc";
import { GrGroup } from "react-icons/gr";
import { BiTrip } from "react-icons/bi";
import { IoMdPricetags } from "react-icons/io";
import { TbGasStation, TbLockAccess } from "react-icons/tb";

import { SearchForm } from "@/components/search-form";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  SidebarRail,
} from "@/components/ui/sidebar";
import Logo from "./logo";
import { NavUser } from "./nav-user";
import { User } from "@/lib/types";
import { usePathname } from "next/navigation";
import { FaCar, FaHouse, FaUserShield, FaScaleBalanced } from "react-icons/fa6";
import { MdEditOff, MdRealEstateAgent } from "react-icons/md";
import { PiMapPinSimpleArea } from "react-icons/pi";
import Link from "next/link";

const data = {
  navMain: [
    {
      title: "Kezdőlap",
      url: "/dashboard",
      icon: FaHouse,
      isActive: true,
    },
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
          icon: VscGraph,
        },
        {
          title: "Feladatok",
          url: "/timesheet/tasks",
          icon: FaTasks,
        },
      ],
    },
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
          title: "Tankolások / Töltések",
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
          title: "Székhely/telephelyek",
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
          url: "/basic-data/travel-reasons",
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
        { title: "Szerepkörök", url: "/admin/roles", icon: TbLockAccess },
        {
          title: "Jogosultságok",
          url: "/admin/permissions",
          icon: MdEditOff,
        },
      ],
    },
  ],
};

interface AppSidebarProps extends React.ComponentProps<typeof Sidebar> {
  user?: User;
}

export function AppSidebar({ user, ...props }: AppSidebarProps) {
  const pathname = usePathname();

  const [searchQuery, setSearchQuery] = React.useState("");

  const baseNav = React.useMemo(() => {
    return data.navMain.filter((item) => {
      if (item.url.startsWith("/admin") && user?.role !== "admin") {
        return false;
      }
      return true;
    });
  }, [user?.role]);

  const filteredNav = React.useMemo(() => {
    const q = searchQuery.trim().toLowerCase();
    if (!q) return baseNav;
    return baseNav
      .map((group) => {
        const groupMatches = group.title.toLowerCase().includes(q);
        const matchedItems = group.items?.filter((sub) =>
          sub.title.toLowerCase().includes(q)
        );
        if (groupMatches) {
          return { ...group };
        }
        if (matchedItems && matchedItems.length) {
          return { ...group, items: matchedItems };
        }
        return null;
      })
      .filter((g): g is (typeof data.navMain)[0] => g !== null);
  }, [searchQuery, baseNav]);


  return (
    <Sidebar {...props}>
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href="/dashboard">
                <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                  <Logo />
                </div>
                <div className="flex flex-col gap-0.5 leading-none">
                  <span className="font-medium">A-Ponton Mérnökiroda Kft.</span>
                </div>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
        <SearchForm onSearch={setSearchQuery} />
      </SidebarHeader>
      <SidebarContent>
        <SidebarGroup>
          <SidebarMenu>
            {filteredNav.map((item, index) => {
              const isActive = pathname === item.url;
              if (!item.items?.length) {
                return (
                  <SidebarMenuItem key={item.title}>
                    <SidebarMenuButton asChild isActive={isActive}>
                      <a
                        href={item.url}
                        className="flex-1 truncate flex items-center gap-2"
                      >
                        {item.icon && <item.icon className="size-4" />}
                        <span className="font-semibold">{item.title}</span>
                      </a>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                );
              }
              return (
                <Collapsible
                  key={item.title}
                  defaultOpen={index === 1}
                  className="group/collapsible"
                >
                  <SidebarMenuItem>
                    <CollapsibleTrigger asChild>
                      <SidebarMenuButton className="font-semibold cursor-pointer">
                        {item.icon && <item.icon className="size-4" />}
                        <span className="font-semibold">{item.title}</span>
                        <Plus className="ml-auto group-data-[state=open]/collapsible:hidden" />
                        <Minus className="ml-auto group-data-[state=closed]/collapsible:hidden" />
                      </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                      <SidebarMenuSub>
                        {item.items.map((item) => {
                          const isActive = pathname === item.url;

                          return (
                            <SidebarMenuSubItem key={item.title}>
                              <SidebarMenuSubButton asChild isActive={isActive}>
                                <a
                                  href={item.url}
                                  className="flex-1 truncate flex items-center gap-2"
                                >
                                  {item.icon && (
                                    <item.icon className="size-4" />
                                  )}
                                  <span>{item.title}</span>
                                </a>
                              </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                          );
                        })}
                      </SidebarMenuSub>
                    </CollapsibleContent>
                  </SidebarMenuItem>
                </Collapsible>
              );
            })}
          </SidebarMenu>
        </SidebarGroup>
      </SidebarContent>
      <SidebarFooter>
        <NavUser />
      </SidebarFooter>
      <SidebarRail />
    </Sidebar>
  );
}

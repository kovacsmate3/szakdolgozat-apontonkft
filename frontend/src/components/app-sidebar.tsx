import * as React from "react";
import {
  Eye,
  Minus,
  MoreHorizontal,
  Plus,
  SquarePen,
  Trash2,
} from "lucide-react";

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
  SidebarMenuAction,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  SidebarRail,
} from "@/components/ui/sidebar";
import Logo from "./logo";
import { NavUser } from "./nav-user";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "./ui/dropdown-menu";
import { User } from "@/lib/types";
import { usePathname } from "next/navigation";

const data = {
  user: {
    name: "shadcn",
    email: "m@example.com",
    avatar: "/avatars/shadcn.jpg",
  },
  navMain: [
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
  ],
};

interface AppSidebarProps extends React.ComponentProps<typeof Sidebar> {
  user?: User;
}

export function AppSidebar({ user, ...props }: AppSidebarProps) {
  const pathname = usePathname();

  return (
    <Sidebar {...props}>
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <a href="">
                <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                  <Logo />
                </div>
                <div className="flex flex-col gap-0.5 leading-none">
                  <span className="font-medium">A-Ponton Mérnökiroda Kft.</span>
                </div>
              </a>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
        <SearchForm />
      </SidebarHeader>
      <SidebarContent>
        <SidebarGroup>
          <SidebarMenu>
            {data.navMain.map((item, index) => {
              const isActive = pathname === item.url;
              if (!item.items?.length) {
                return (
                  <SidebarMenuItem key={item.title}>
                    <SidebarMenuButton asChild isActive={isActive}>
                      <a href={item.url} className="flex-1 truncate">
                        {item.title}
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
                        {item.title}{" "}
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
                                <a href={item.url} className="flex-1 truncate">
                                  {item.title}
                                </a>
                              </SidebarMenuSubButton>
                              <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                  <SidebarMenuAction showOnHover>
                                    <MoreHorizontal className="size-4" />
                                    <span className="sr-only">
                                      További lehetőségek
                                    </span>
                                  </SidebarMenuAction>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                  side="right"
                                  align="start"
                                  className="w-48 rounded-lg"
                                >
                                  <DropdownMenuItem>
                                    <Eye className="text-muted-foreground" />
                                    <span>Megtekintés</span>
                                  </DropdownMenuItem>
                                  <DropdownMenuItem>
                                    <SquarePen className="text-muted-foreground" />
                                    <span>Szerkesztés</span>
                                  </DropdownMenuItem>
                                  <DropdownMenuSeparator />
                                  <DropdownMenuItem>
                                    <Trash2 className="text-muted-foreground" />
                                    <span>Törlés</span>
                                  </DropdownMenuItem>
                                </DropdownMenuContent>
                              </DropdownMenu>
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
        <NavUser
          user={{
            name: user?.name ?? "Ismeretlen",
            email: user?.email ?? "Nincs email",
            avatar: user?.image ?? "/default-avatar.png",
          }}
        />
      </SidebarFooter>
      <SidebarRail />
    </Sidebar>
  );
}

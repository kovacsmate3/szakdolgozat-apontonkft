import * as React from "react";
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
import Link from "next/link";
import { Minus, Plus } from "lucide-react";
import { navMain } from "@/lib/data/nav.config";

interface AppSidebarProps extends React.ComponentProps<typeof Sidebar> {
  user?: User;
}

export function AppSidebar({ user, ...props }: AppSidebarProps) {
  const pathname = usePathname();

  const [searchQuery, setSearchQuery] = React.useState("");

  const baseNav = React.useMemo(() => {
    return navMain.filter((item) => {
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
      .filter((g): g is (typeof navMain)[0] => g !== null);
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
                      <Link
                        href={item.url}
                        className="flex-1 truncate flex items-center gap-2"
                      >
                        {item.icon && <item.icon className="size-4" />}
                        <span className="font-semibold">{item.title}</span>
                      </Link>
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
                                <Link
                                  href={item.url}
                                  className="flex-1 truncate flex items-center gap-2"
                                >
                                  {item.icon && (
                                    <item.icon className="size-4" />
                                  )}
                                  <span>{item.title}</span>
                                </Link>
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

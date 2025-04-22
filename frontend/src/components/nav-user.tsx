"use client";

import { ChevronsUpDown, LogOut, UserCog } from "lucide-react";

import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from "@/components/ui/sidebar";
import { signOut, useSession } from "next-auth/react";
import Link from "next/link";
import { useTheme } from "next-themes";
import { useQuery } from "@tanstack/react-query";
import { getUser } from "@/server/users";

const getInitials = (name: string) => {
  if (!name || name.trim() === "") return "NU";

  const parts = name.trim().split(" ");
  if (parts.length === 1) {
    return parts[0][0]?.toUpperCase() ?? "";
  }
  return (parts[0][0] + parts[1][0]).toUpperCase();
};

export function NavUser() {
  const { theme } = useTheme();
  const { isMobile } = useSidebar();

  const { data: session } = useSession();

  const defaultAvatar =
    theme === "light"
      ? "/images/(auth)/default-avatar-light.png"
      : "/images/(auth)/default-avatar-dark.png";

  // useQuery-t mindig meghívjuk, csak feltételesen engedélyezzük
  const { data: freshUserData } = useQuery({
    queryKey: ["user", session?.user?.id, session?.user?.access_token],
    queryFn: () => {
      if (!session?.user?.id || !session?.user?.access_token) {
        return null;
      }
      return getUser({
        userId: session.user.id,
        token: session.user.access_token,
      });
    },
    staleTime: 10 * 1000, // 10 másodperc
    refetchOnWindowFocus: true,
    enabled: !!session?.user?.id && !!session?.user?.access_token,
  });

  // Ha nincs session, ne jelenítsen meg semmit
  if (!session?.user) return null;

  // Biztonságos hozzáférés a friss adatokhoz
  const userName = freshUserData
    ? `${freshUserData.lastname} ${freshUserData.firstname}`.trim()
    : session.user.name || "Ismeretlen";

  const userEmail = freshUserData?.email || session.user.email || "Nincs email";
  const userAvatar = session.user.image || defaultAvatar;

  return (
    <SidebarMenu>
      <SidebarMenuItem>
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <SidebarMenuButton
              size="lg"
              className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            >
              <Avatar className="h-8 w-8 rounded-lg">
                <AvatarImage src={userAvatar} alt={userName} />
                <AvatarFallback className="rounded-lg">
                  {getInitials(userName)}
                </AvatarFallback>
              </Avatar>
              <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{userName}</span>
                <span className="truncate text-xs">{userEmail}</span>
              </div>
              <ChevronsUpDown className="ml-auto size-4" />
            </SidebarMenuButton>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
            side={isMobile ? "bottom" : "right"}
            align="end"
            sideOffset={4}
          >
            <DropdownMenuLabel className="p-0 font-normal">
              <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                <Avatar className="h-8 w-8 rounded-lg">
                  <AvatarImage src={userAvatar} alt={userName} />
                  <AvatarFallback className="rounded-lg">
                    {getInitials(userName)}
                  </AvatarFallback>
                </Avatar>
                <div className="grid flex-1 text-left text-sm leading-tight">
                  <span className="truncate font-medium">{userName}</span>
                  <span className="truncate text-xs">{userEmail}</span>
                </div>
              </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
              <Link href="/profile" passHref>
                <DropdownMenuItem>
                  <UserCog className="mr-2 h-4 w-4" />
                  <span>Profilom</span>
                </DropdownMenuItem>
              </Link>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem onSelect={() => signOut({ callbackUrl: "/" })}>
              <LogOut />
              Kijelentkezés
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarMenuItem>
    </SidebarMenu>
  );
}

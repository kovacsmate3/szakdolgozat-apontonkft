"use client";

import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";

import { AlignJustify } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";

export default function MobileNav() {
  const [isOpen, setIsOpen] = useState(false);

  const pathanme = usePathname();

  const menuItems = [
    { title: "Kezdőlap", href: "/home", number: "01" },
    { title: "Referenciáink", href: "/references", number: "02" },
    { title: "Munkaeszközeink", href: "/capital-equipment", number: "03" },
    { title: "Kapcsolat", href: "/contact", number: "04" },
  ];

  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth >= 768) {
        setIsOpen(false);
      }
    };

    const handleLoadingScreen = () => {
      setIsOpen(false);
    };

    window.addEventListener("resize", handleResize);
    window.addEventListener("loadingScreenVisible", handleLoadingScreen);

    return () => {
      window.removeEventListener("resize", handleResize);
      window.removeEventListener("loadingScreenVisible", handleLoadingScreen);
    };
  }, []);

  const handleLinkClick = () => {
    setIsOpen(false);
  };

  return (
    <div className="lg:hidden">
      <Sheet open={isOpen} onOpenChange={setIsOpen}>
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger asChild>
              <SheetTrigger asChild>
                <Button
                  variant="outline"
                  size="icon"
                  className="cursor-pointer"
                >
                  <AlignJustify size={24} />
                </Button>
              </SheetTrigger>
            </TooltipTrigger>
            <TooltipContent>
              <p>Navigációs menü</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
        <SheetContent
          side="top"
          className="p-4 bg-background text-foreground m-2"
        >
          <SheetHeader>
            <SheetTitle></SheetTitle>
            <SheetDescription></SheetDescription>
          </SheetHeader>
          {menuItems.map((item, index) => {
            const isActive = pathanme === item.href;

            return (
              <div
                key={index}
                className={`flex items-center justify-between p-4 rounded-lg hover:bg-gray-100 
                  focus:ring-gray-50 dark:hover:bg-zinc-400/60 dark:focus:ring-zinc-400/80  ${isActive ? "bg-gray-200 dark:bg-zinc-500/60 hover:bg-gray-200 dark:hover:bg-zinc-500/60  " : ""}`}
              >
                <Link
                  href={item.href}
                  className="text-2xl hover:text-primary font-medium"
                  onClick={handleLinkClick}
                >
                  {item.title}
                </Link>
                <span className="text-lg text-black/70 dark:text-white/70 font-light">
                  {item.number}
                </span>
              </div>
            );
          })}
        </SheetContent>
      </Sheet>
    </div>
  );
}

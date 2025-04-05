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
import { AlignJustify } from "lucide-react";
import Link from "next/link";

export default function MobileNav() {
  const [isOpen, setIsOpen] = useState(false);

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
    <div className="md:hidden">
      <Sheet open={isOpen} onOpenChange={setIsOpen}>
        <SheetTrigger asChild>
          <Button variant="outline" size="icon">
            <AlignJustify size={24} />
          </Button>
        </SheetTrigger>
        <SheetContent
          side="top"
          className="p-4 bg-background text-foreground m-2"
        >
          <SheetHeader>
            <SheetTitle></SheetTitle>
            <SheetDescription></SheetDescription>
          </SheetHeader>
          {menuItems.map((item, index) => (
            <div key={index} className="flex items-center justify-between">
              <Link
                href={item.href}
                className="text-2xl hover:text-primary font-medium"
                onClick={handleLinkClick}
              >
                {item.title}
              </Link>
              <span className="text-lg text-muted-foreground font-light">
                {item.number}
              </span>
            </div>
          ))}
        </SheetContent>
      </Sheet>
    </div>
  );
}

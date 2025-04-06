"use client";

import React, { useEffect, useState } from "react";
import Link from "next/link";
import { LogIn } from "lucide-react";
import { Button } from "@/components/ui/button";
import { ThemeToggle } from "@/components/theme-toggle";
import Logo from "@/components/logo";

const StickyMainNav = () => {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 100) {
        setIsVisible(true);
      } else {
        setIsVisible(false);
      }
    };

    window.addEventListener("scroll", handleScroll);

    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  return (
    <div
      className={`fixed top-0 left-0 right-0 w-full z-40 transition-all duration-300 mt-2 ${
        isVisible ? "translate-y-0 opacity-100" : "-translate-y-full opacity-0"
      }`}
    >
      {/* Pontosan ugyanolyan szerkezetű div mint a Header-ben, de csak a középső rész látható */}
      <div className="h-19 full-width-container px-4 flex items-center justify-between">
        {/* Bal oldal - abszolút pozícióval nem foglal helyet, de megőrzi a pozíciót */}
        <div className="opacity-0 pointer-events-none">
          <Link href="/home" className="flex items-center gap-2">
            <Logo />
            <span className="text-xl lg:text-2xl font-bold">A-Ponton Kft.</span>
          </Link>
        </div>

        {/* Középső rész - ez az egyetlen látható elem */}
        <div className="hidden md:flex justify-center flex-1">
          <nav
            className="flex items-center gap-4 ml-8 md:text-lg lg:text-xl 
    bg-neutral-100/65 text-black dark:text-white dark:bg-neutral-900/65
    backdrop-blur-sm shadow-md p-5 rounded-md"
          >
            <Link
              href="/home"
              className=" 
         dark:hover:text-neutral-300
        hover:text-neutral-600 
        transition-colors 
        relative 
        group"
            >
              Kezdőlap
              <span
                className="absolute bottom-0 left-0 w-0 h-0.5 
        bg-neutral-500 dark:bg-neutral-300 
        transition-all duration-300 
        group-hover:w-full"
              ></span>
            </Link>
            <Link
              href="/references"
              className=" 
     dark:hover:text-neutral-300
        hover:text-neutral-600  
        transition-colors 
        relative 
        group"
            >
              Referenciáink
              <span
                className="absolute bottom-0 left-0 w-0 h-0.5 
        bg-neutral-500 dark:bg-neutral-300  
        transition-all duration-300 
        group-hover:w-full"
              ></span>
            </Link>
            <Link
              href="/capital-equipment"
              className="
         dark:hover:text-neutral-300
        hover:text-neutral-600  
        transition-colors 
        relative 
        group"
            >
              Munkaeszközeink
              <span
                className="absolute bottom-0 left-0 w-0 h-0.5 
        bg-neutral-500 dark:bg-neutral-300 
        transition-all duration-300 
        group-hover:w-full"
              ></span>
            </Link>
            <Link
              href="/contact"
              className=" 
     dark:hover:text-neutral-300
        hover:text-neutral-600
        transition-colors 
        relative 
        group"
            >
              Kapcsolat
              <span
                className="absolute bottom-0 left-0 w-0 h-0.5 
        bg-neutral-500 dark:bg-neutral-300  
        transition-all duration-300 
        group-hover:w-full"
              ></span>
            </Link>
          </nav>
        </div>

        {/* Jobb oldal - abszolút pozícióval nem foglal helyet, de megőrzi a pozíciót */}
        <div className="opacity-0 pointer-events-none">
          <div className="flex items-center gap-2">
            <Link href="/login">
              <Button variant="outline" size="icon" className="cursor-pointer">
                <LogIn size={20} />
                <span className="sr-only">Bejelentkezés</span>
              </Button>
            </Link>
            <ThemeToggle />
          </div>
        </div>
      </div>
    </div>
  );
};

export default StickyMainNav;

import { ThemeToggle } from "@/components/theme-toggle";
import MainNav from "./header/MainNav";
import MobileNav from "./header/MobileNav";
import Link from "next/link";
import { LogIn } from "lucide-react";
import { Button } from "../ui/button";
import Logo from "../logo";
//import StickyMainNav from "./header/StickyMainNav";

const Header = () => {
  return (
    <>
      <header className="absolute z-40 top-0 w-full border-b bg-white dark:bg-black">
        <div className="h-19 full-width-container px-4 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Link href="/home" className="flex items-center gap-2">
              <Logo />
              <span className="text-xl lg:text-2xl font-bold">
                A-Ponton Kft.
              </span>
            </Link>
          </div>

          <div className="hidden md:flex justify-center flex-1">
            <MainNav />
          </div>

          <div className="flex items-center gap-2">
            <MobileNav />
            <Link href="/login">
              <Button variant="outline" size="icon" className="cursor-pointer">
                <LogIn size={20} />
                <span className="sr-only">Bejelentkezés</span>
              </Button>
            </Link>
            <ThemeToggle />
          </div>
        </div>
      </header>
      {/*<StickyMainNav />*/}
    </>
  );
};

export default Header;

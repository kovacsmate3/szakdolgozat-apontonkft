import { ThemeToggle } from "@/components/theme-toggle";
import MainNav from "./header/MainNav";
import MobileNav from "./header/MobileNav";
import Link from "next/link";
import { LogIn } from "lucide-react";
import { Button } from "../ui/button";
import Logo from "../logo";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";

const Header = () => {
  return (
    <header className="absolute z-40 top-0 w-full border-b bg-white dark:bg-black">
      <div className="h-19 full-width-container px-4 flex items-center justify-between">
        <div className="flex items-center gap-2 flex-1">
          <Link href="/home" className="flex items-center gap-2 cursor-default">
            <Logo />
            <span className="text-xl lg:text-2xl font-bold">A-Ponton Kft.</span>
          </Link>
        </div>

        <div className="hidden lg:flex justify-center flex-1">
          <MainNav />
        </div>

        <div className="flex justify-end gap-2 flex-1">
          <MobileNav />
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <Link href="/login">
                  <Button
                    variant="outline"
                    size="icon"
                    className="cursor-pointer"
                  >
                    <LogIn size={20} />
                    <span className="sr-only">Bejelentkezés</span>
                  </Button>
                </Link>
              </TooltipTrigger>
              <TooltipContent>
                <p>Bejelentkezés</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
          <ThemeToggle />
        </div>
      </div>
    </header>
  );
};

export default Header;

"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

const MainNav = () => {
  const pathname = usePathname();

  const menuItems = [
    { title: "Kezdőlap", href: "/home" },
    { title: "Referenciáink", href: "/references" },
    { title: "Munkaeszközeink", href: "/capital-equipment" },
    { title: "Kapcsolat", href: "/contact" },
  ];

  return (
    <>
      <nav className="flex items-center gap-4 md:text-lg lg:text-xl">
        {menuItems.map((item) => {
          const isActive = pathname === item.href;

          return (
            <Link
              key={item.href}
              href={item.href}
              className={`p-2 rounded-md hover:bg-gray-100 
                  focus:ring-gray-50 dark:hover:bg-zinc-400/60 dark:focus:ring-zinc-400/80  ${isActive ? "bg-gray-200 dark:bg-zinc-500/60 hover:bg-gray-200 dark:hover:bg-zinc-500/60  " : ""}`}
            >
              {" "}
              {item.title}
            </Link>
          );
        })}
      </nav>
    </>
  );
};
export default MainNav;

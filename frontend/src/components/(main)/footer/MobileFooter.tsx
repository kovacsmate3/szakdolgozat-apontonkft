"use client";

import Logo from "@/components/logo";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { FaEnvelope, FaFacebook, FaInstagram } from "react-icons/fa";
import { FaLocationDot, FaPhone } from "react-icons/fa6";

const MobileFooter = () => {
  const pathname = usePathname();
  return (
    <div className="flex flex-col items-center gap-4 text-center text-gray-600 dark:text-gray-400">
      <div className="mb-2 text-center flex flex-col items-center">
        <div className="flex justify-center">
          <Logo className="mb-2 w-13 h-13" />
        </div>
        <p className="uppercase font-extrabold md:text-xl text-black dark:text-white">
          A-Ponton Mérnökiroda Kft.
        </p>
        <p className="italic font-light">Míg mások építenek, mi mérünk.</p>
        <div className="flex flex-col items-start gap-1 mt-4">
          <div className="flex items-center gap-2">
            <FaPhone size={24} className="min-w-[24px]" />
            <span>+36 20 927 0324</span>
          </div>
          <div className="flex items-center gap-2">
            <FaEnvelope size={24} className="min-w-[24px]" />
            <a
              href="mailto:aponton@t-online.hu"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Email"
              className="dark:hover:text-white hover:text-black underline"
            >
              aponton@t-online.hu
            </a>
          </div>
          <div className="flex items-center gap-2">
            <FaLocationDot size={24} className="min-w-[24px]" />
            <span>1151 Budapest, Esthajnal utca 3.</span>
          </div>
        </div>
      </div>
      <div className="flex justify-around w-full">
        <div className="flex flex-col items-center">
          <h3 className="uppercase font-extrabold text-black dark:text-white">
            Navigáció
          </h3>
          <Link
            href="/home"
            className={`dark:hover:text-white hover:text-black ${
              pathname === "/home"
                ? "font-bold underline text-black dark:text-white"
                : ""
            }`}
          >
            Kezdőlap
          </Link>
          <Link
            href="/references"
            className={`dark:hover:text-white hover:text-black ${
              pathname === "/references"
                ? "font-bold underline text-black dark:text-white"
                : ""
            }`}
          >
            Referenciáink
          </Link>
          <Link
            href="/capital-equipment"
            className={`dark:hover:text-white hover:text-black ${
              pathname === "/capital-equipment"
                ? "font-bold underline text-black dark:text-white"
                : ""
            }`}
          >
            Munkaeszközeink
          </Link>
          <Link
            href="/contact"
            className={`dark:hover:text-white hover:text-black ${
              pathname === "/contact"
                ? "font-bold underline text-black dark:text-white"
                : ""
            }`}
          >
            Kapcsolat
          </Link>
        </div>
        <div className="flex flex-col gap-1 items-center">
          <h3 className="uppercase font-extrabold text-black dark:text-white">
            Kövess minket!
          </h3>
          <div className="flex gap-3">
            <a
              href="https://www.instagram.com/aponton_kft/"
              className="dark:hover:text-white hover:text-black"
              title="A-Ponton Mérnökiroda Kft. Instagram oldala"
            >
              <FaInstagram size={24} />
            </a>
            <a
              href="https://www.facebook.com/A-Ponton-M%C3%A9rn%C3%B6kiroda-Kft-331914836900202"
              className="dark:hover:text-white hover:text-black"
              title="A-Ponton Mérnökiroda Kft. Facebook oldala"
            >
              <FaFacebook size={24} />
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};
export default MobileFooter;

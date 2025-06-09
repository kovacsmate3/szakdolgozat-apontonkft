"use client";

import { useEffect, useState } from "react";
import Image from "next/image";

const HeroSection = () => {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    setIsVisible(true);
    const handleLoadingScreenEnd = () => {
      setIsVisible(false);
      setTimeout(() => setIsVisible(true), 50);
    };

    window.addEventListener("loadingScreenEnd", handleLoadingScreenEnd);

    return () => {
      window.removeEventListener("loadingScreenEnd", handleLoadingScreenEnd);
    };
  }, []);

  return (
    <div className="relative w-full h-screen overflow-hidden">
      <div className="absolute inset-0 z-0">
        <Image
          src="/images/main/home/hero-background.png"
          alt="Geodéziai mérési háttér"
          fill
          priority
          className="object-cover object-right opacity-50"
        />
      </div>
      <div className="relative z-10 flex flex-col justify-center items-start h-full px-8 md:px-16">
        <h1
          className={`uppercase text-4xl md:text-7xl font-extrabold text-foreground ${
            isVisible ? "pop-up" : ""
          }`}
        >
          Jövőd<br></br>stabil<br></br>A-Ponton.
        </h1>
        <p
          className={`mt-2 text-xl md:text-2xl font-light text-foreground ${
            isVisible ? "pop-up delay-100" : ""
          }`}
        >
          Míg mások építenek, mi mérünk.
        </p>
        <p
          className={`text-lg md:text-xl italic text-foreground ${
            isVisible ? "pop-up delay-200" : ""
          }`}
        >
          Minket a precíz munkánkért szeretnek.
        </p>
      </div>
    </div>
  );
};

export default HeroSection;

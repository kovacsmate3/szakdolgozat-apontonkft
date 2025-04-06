import AboutSection from "@/components/(main)/main/home/AboutSection";
import HeroSection from "@/components/(main)/main/home/HeroSection";
import { Metadata } from "next";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Kezdőlap",
};

export default function HomePage() {
  return (
    <>
      <HeroSection />
      <AboutSection />
    </>
  );
}

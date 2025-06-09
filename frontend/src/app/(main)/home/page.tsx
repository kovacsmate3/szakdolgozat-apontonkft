import AboutSection from "@/components/(main)/home/about/AboutSection";
import HeroSection from "@/components/(main)/home/HeroSection";
import PartnerSection from "@/components/(main)/home/partner/PartnerSection";
import { Metadata } from "next";

export const metadata: Metadata = {
  title: "A-Ponton Kft. - Kezdőlap",
};

export default function HomePage() {
  return (
    <>
      <HeroSection />
      <AboutSection />
      <PartnerSection />
    </>
  );
}

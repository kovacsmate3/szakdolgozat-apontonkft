"use client";

import { useTheme } from "next-themes";
import Image from "next/image";
import { useEffect, useState } from "react";

interface LogoProps {
  className?: string;
}

const Logo = ({ className = "" }: LogoProps) => {
  const { theme, resolvedTheme } = useTheme();
  const [logoSrc, setLogoSrc] = useState("/images/logo_light.jpg");
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  useEffect(() => {
    if (mounted) {
      const currentTheme = theme === "system" ? resolvedTheme : theme;
      setLogoSrc(
        currentTheme === "dark"
          ? "/images/logo_dark.jpg"
          : "/images/logo_light.jpg"
      );
    }
  }, [theme, resolvedTheme, mounted]);

  if (!mounted) return null;

  return (
    <Image
      src={logoSrc}
      alt="A-Ponton Kft. Logó"
      title="A-Ponton Kft. Logó"
      width={46}
      height={46}
      className={className}
      priority
    />
  );
};

export default Logo;

"use client";

import { useEffect, useState, useRef } from "react";
import LoadingScreen from "./loading-screen";

export default function ClientWrapper({
  children,
}: {
  children: React.ReactNode;
}) {
  const [showLoading, setShowLoading] = useState(false);
  const [prevWidth, setPrevWidth] = useState<number>(
    typeof window !== "undefined" ? window.innerWidth : 0
  );
  const resizeTimeout = useRef<number | null>(null);

  useEffect(() => {
    const handleResize = () => {
      const currentWidth = window.innerWidth;
      if (Math.abs(currentWidth - prevWidth) >= 100) {
        setShowLoading(true);
        setPrevWidth(currentWidth);

        if (resizeTimeout.current) {
          clearTimeout(resizeTimeout.current);
        }

        resizeTimeout.current = setTimeout(() => {
          setShowLoading(false);
        }, 1500);
      }
    };

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, [prevWidth]);

  return (
    <>
      {showLoading && <LoadingScreen />}
      {children}
    </>
  );
}

"use client";

import { useEffect, useState, useRef } from "react";
import { Progress } from "@/components/ui/progress";

export default function LoadingScreen() {
  const [progress, setProgress] = useState(0);
  const [visible, setVisible] = useState(false);
  const [prevWidth, setPrevWidth] = useState<number>(window.innerWidth);
  const resizeTimeout = useRef<NodeJS.Timeout | null>(null);

  useEffect(() => {
    const handleResize = () => {
      const currentWidth = window.innerWidth;

      if (Math.abs(currentWidth - prevWidth) < 100) return;

      setVisible(true);
      setProgress(0);
      setPrevWidth(currentWidth);

      window.dispatchEvent(new CustomEvent("loadingScreenVisible"));

      if (resizeTimeout.current) {
        clearTimeout(resizeTimeout.current);
      }

      const interval = setInterval(() => {
        setProgress((prev) => {
          if (prev >= 100) {
            clearInterval(interval);
            setTimeout(() => {
              setVisible(false);
              window.dispatchEvent(new Event("loadingScreenEnd"));
            }, 300);
            return 100;
          }
          return prev + 5;
        });
      }, 50);

      resizeTimeout.current = setTimeout(() => {
        setVisible(false);
      }, 1500);

      return () => clearInterval(interval);
    };

    window.addEventListener("resize", handleResize);
    handleResize();

    return () => {
      window.removeEventListener("resize", handleResize);
    };
  }, [prevWidth]);

  if (!visible) return null;

  return (
    <div className="fixed inset-0 bg-background text-foreground flex items-center justify-center z-50">
      <div className="text-center">
        <div className="text-4xl md:text-6xl font-bold">A-Ponton Kft.</div>
        <div className="w-3/4 md:w-4/5 mx-auto mt-4">
          <Progress value={progress} className="h-1" />
        </div>
      </div>
    </div>
  );
}

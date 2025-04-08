"use client";

import Image from "next/image";
import Autoplay from "embla-carousel-autoplay";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/ui/carousel";
import { Card, CardContent } from "@/components/ui/card";
import { useRef } from "react";
import { ImageData } from "@/lib/types";

interface ImageCarouselProps {
  images: ImageData[];
}

const ImageCarousel: React.FC<ImageCarouselProps> = ({ images }) => {
  const plugin = useRef(
    Autoplay({
      delay: 5000,
      stopOnInteraction: true,
      stopOnMouseEnter: false,
    })
  );

  return (
    <div className="w-full">
      <Carousel
        plugins={[plugin.current]}
        opts={{
          loop: true,
          align: "start",
        }}
        className="w-full"
      >
        <CarouselContent>
          {images.map((image, index) => (
            <CarouselItem key={index}>
              <div className="p-1">
                <Card className="border-0 shadow-sm overflow-hidden">
                  <CardContent className="p-0">
                    <div className="relative aspect-video">
                      <Image
                        src={image.src}
                        alt={image.alt}
                        title={image.title}
                        width={image.width}
                        height={image.height}
                        className="object-cover w-full h-full"
                      />
                    </div>
                  </CardContent>
                </Card>
              </div>
            </CarouselItem>
          ))}
        </CarouselContent>
        <CarouselPrevious className="absolute left-2 bg-white/80 hover:bg-white dark:bg-gray-800/80 dark:hover:bg-gray-800" />
        <CarouselNext className="absolute right-2 bg-white/80 hover:bg-white dark:bg-gray-800/80 dark:hover:bg-gray-800" />
      </Carousel>
    </div>
  );
};
export default ImageCarousel;

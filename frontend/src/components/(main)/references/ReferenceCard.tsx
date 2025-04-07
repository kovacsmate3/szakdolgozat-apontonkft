import { Reference } from "@/lib/types";
import Image from "next/image";

const ReferenceCard = ({ image, title, description }: Reference) => {
  return (
    <div className="py-4">
      <div className="rounded-lg shadow-md overflow-hidden">
        <a href={image.originalSrc} target="_blank" rel="noopener noreferrer">
          <Image
            src={image.src}
            alt={image.alt}
            title={image.title}
            width={800}
            height={600}
            priority
            className="object-cover w-full h-full transition-transform duration-300 hover:scale-105"
          />
        </a>
        <div className="p-4 bg-white dark:bg-neutral-900 hyphens-auto">
          <h3 className="text-lg sm:text-xl font-semibold mb-2">{title}</h3>
          <p className="text-gray-600 dark:text-gray-400 text-justify">
            {description}
          </p>
        </div>
      </div>
    </div>
  );
};
export default ReferenceCard;

import { partners } from "@/lib/data/partner-section-data";
import Image from "next/image";

const PartnerSection = () => {
  return (
    <div className="full-width-container mx-auto px-8 md:px-16 pt-6 pb-12 bg-gray-200 dark:bg-zinc-400/60">
      <h1 className="text-center lg:text-left text-2xl sm:text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold mb-8">
        Partnereink
      </h1>
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {partners.map((partner, index) => (
            <a
              key={index}
              href={partner.link}
              target="_blank"
              rel="noopener noreferrer"
              className="group flex flex-col items-center text-center p-4 bg-white dark:bg-neutral-900 shadow-md rounded-lg transform transition duration-300 hover:scale-105 hover:shadow-xl"
            >
              <Image
                src={partner.imgSrc}
                alt={partner.alt}
                width={150}
                height={150}
                className="border border-black dark:border-0 rounded-lg mb-4 object-contain h-28 w-28"
              />
              <p className="text-lg text-gray-600 dark:text-gray-400 font-medium group-hover:text-black dark:group-hover:text-white">
                {partner.name}
              </p>
            </a>
          ))}
        </div>
      </div>
    </div>
  );
};

export default PartnerSection;

import { aboutImages, servicesImages } from "@/lib/data/about-section-data";
import AboutUsText from "./AboutUsText";
import ImageCarousel from "./ImageCarousel";
import ServicesList from "./ServicesList";

const DesktopAbout: React.FC = () => (
  <div className="hidden lg:block mb-12">
    <div className="grid grid-cols-2 gap-8 xl:gap-12 2xl:gap-16">
      <div className="grid grid-rows-2 gap-8 xl:gap-12 2xl:gap-16 h-full">
        <div className="flex flex-col justify-center">
          <h2 className="text-2xl md:text-3xl lg:text-4xl 2xl:text-5xl font-semibold mb-6 text-center">
            Történetünk
          </h2>
          <div className="max-w-3xl mx-auto">
            <AboutUsText textSize="md:text-lg lg:text-xl 2xl:text-2xl" />
          </div>
        </div>

        <div className="w-full h-full flex items-center">
          <ImageCarousel images={servicesImages} />
        </div>
      </div>

      <div className="grid grid-rows-2 gap-8 xl:gap-12 2xl:gap-16 h-full">
        <div className="w-full h-full flex items-center">
          <ImageCarousel images={aboutImages} />
        </div>

        <div className="flex flex-col justify-center">
          <h2 className="text-2xl md:text-3xl lg:text-4xl 2xl:text-5xl font-semibold mb-6 text-center">
            Szolgáltatásaink
          </h2>
          <div className="max-w-3xl mx-auto">
            <ServicesList textSize="md:text-lg lg:text-xl 2xl:text-2xl" />
          </div>
        </div>
      </div>
    </div>
  </div>
);
export default DesktopAbout;

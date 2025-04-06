import { aboutImages, servicesImages } from "@/lib/data/about-section-data";
import AboutUsText from "./AboutUsText";
import ImageCarousel from "./ImageCarousel";
import ServicesList from "./ServicesList";

const MobileAbout: React.FC = () => (
  <div className="lg:hidden flex flex-col gap-8 mb-12">
    <div>
      <h2 className="text-2xl font-semibold mb-2 text-center">Történetünk</h2>
      <AboutUsText />
      <div className="w-full mb-4">
        <ImageCarousel images={aboutImages} />
      </div>
    </div>

    <div>
      <h2 className="text-2xl font-semibold mb-2 text-center">
        Szolgáltatásaink
      </h2>
      <div className="mb-4">
        <ServicesList />
      </div>
      <div className="w-full">
        <ImageCarousel images={servicesImages} />
      </div>
    </div>
  </div>
);
export default MobileAbout;

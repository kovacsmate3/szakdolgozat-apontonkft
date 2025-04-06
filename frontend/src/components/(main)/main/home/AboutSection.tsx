import DesktopAbout from "./about/DesktopAbout";
import MobileAbout from "./about/MobileAbout";

const AboutSection = () => {
  return (
    <div>
      <div className="full-width-container mx-auto px-4">
        <h1 className="md:text-4xl lg:text-5xl 2xl:text-6xl font-bold mb-6 ml-4 mt-6">
          Rólunk
        </h1>
        <DesktopAbout />
        <MobileAbout />
      </div>
    </div>
  );
};

export default AboutSection;

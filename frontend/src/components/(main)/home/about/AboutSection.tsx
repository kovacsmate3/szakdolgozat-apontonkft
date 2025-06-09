import DesktopAbout from "../about/DesktopAbout";
import MobileAbout from "../about/MobileAbout";

const AboutSection = () => {
  return (
    <div>
      <div className="full-width-container mx-auto px-8 md:px-16">
        <h1 className="text-center lg:text-left text-2xl sm:text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold my-6">
          Rólunk
        </h1>
        <DesktopAbout />
        <MobileAbout />
      </div>
    </div>
  );
};

export default AboutSection;

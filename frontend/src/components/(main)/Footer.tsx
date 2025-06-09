import DesktopFooter from "./footer/DesktopFooter";
import MobileFooter from "./footer/MobileFooter";

const Footer = () => {
  return (
    <footer className="footer p-6 bg-background border-t border-gray-600 dark:border-gray-400 w-full text-gray-600 dark:text-gray-400">
      <div className="hidden md:block">
        <DesktopFooter />
      </div>
      <div className="block md:hidden">
        <MobileFooter />
      </div>
      <hr className="border-t border-gray-600 dark:border-gray-400 my-4 mx-auto" />
      <p className="text-sm text-center my-4">
        &copy; 2003-{new Date().getFullYear()} A-Ponton Mérnökiroda Kft.{" "}
        <br className="sm:hidden" />
        Minden jog fenntartva.
      </p>
    </footer>
  );
};

export default Footer;

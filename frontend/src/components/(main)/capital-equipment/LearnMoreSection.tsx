import Link from "next/link";
import { FaRegFilePdf } from "react-icons/fa";

const LearnMoreSection = () => {
  return (
    <div className="text-center py-8">
      <Link
        href="/documents/epuletfelmeres.pdf"
        target="_blank"
        className="group inline-block bg-black/80 hover:bg-black text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-300"
      >
        <span className="block sm:inline whitespace-nowrap mb-2 sm:mb-0">
          Tudj meg többet a lézerszkennelésről:
        </span>

        <span className="sm:ml-2 hidden sm:inline"> </span>

        <span className="inline-flex items-center whitespace-nowrap group-hover:text-gray-300 transition-colors duration-300">
          <span className="underline">
            Épület teljes felmérése 3D lézerszkenneléssel
          </span>
          <FaRegFilePdf className="ml-2 text-xl flex-shrink-0" />
        </span>
      </Link>
    </div>
  );
};

export default LearnMoreSection;

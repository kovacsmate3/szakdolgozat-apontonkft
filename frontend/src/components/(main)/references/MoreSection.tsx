import VideoPlayer from "./VideoPlayer";

const MoreSection = () => {
  return (
    <div>
      <h1 className="text-center lg:text-left text-2xl sm:text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold mb-6">
        Bővebben
      </h1>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 justify-items-center">
        <div className="w-full max-w-lg p-2">
          <VideoPlayer videoId="DPSuofN1LxA" title="Nézz körül a BudaParton!" />
        </div>
        <div className="w-full max-w-lg p-2">
          <video
            className="w-full aspect-video rounded-md object-cover"
            controls
            preload="metadata"
            poster="/images/(main)/references/poster.png"
          >
            <source src="/videos/eon3d_varazsdoboz.mp4" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
        </div>
        <div className="w-full max-w-lg p-2">
          <VideoPlayer videoId="Bfy4egr1Dq4" title="LIBERTY by WING" />
        </div>
      </div>
      <div className="text-gray-600 dark:text-gray-200 text-center mt-4">
        <a
          className="text-brown-700 hover:underline hover:text-black dark:hover:text-white"
          href="/html/videoleiras.html"
          target="_blank"
          title="Hogyan készült az EON karácsonyi 3D térhatású varázsdoboza? videó szöveges leírása"
        >
          Kattints a Hogyan készült az EON karácsonyi 3D térhatású varázsdoboza?
          videó szöveges leírásának megtekintéséhez
        </a>
      </div>
    </div>
  );
};

export default MoreSection;

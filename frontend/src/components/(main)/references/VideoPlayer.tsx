"use client";

import YouTube, { YouTubeProps } from "react-youtube";

interface VideoPlayerProps {
  videoId: string;
  title: string;
}

const VideoPlayer: React.FC<VideoPlayerProps> = ({ videoId, title }) => {
  const opts: YouTubeProps["opts"] = {
    height: "100%",
    width: "100%",
    playerVars: {
      autoplay: 0,
      enablejsapi: 1,
    },
  };

  const onReady: YouTubeProps["onReady"] = (event) => {
    event.target.pauseVideo();
  };

  const onError: YouTubeProps["onError"] = (event) => {
    console.error("YouTube Player Error: ", event.data);
  };

  return (
    <div className="w-full aspect-[16/9] rounded-md overflow-hidden">
      <YouTube
        videoId={videoId}
        opts={opts}
        title={title}
        onReady={onReady}
        onError={onError}
        className="w-full h-full rounded-md"
      />
    </div>
  );
};

export default VideoPlayer;

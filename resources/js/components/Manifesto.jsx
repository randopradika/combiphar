import { useMemo, useRef, useState } from "react";

function getYouTubeId(url = "") {
  const match = url.match(
    /(?:youtube\.com\/watch\?v=|youtube\.com\/embed\/|youtu\.be\/)([^&?/]+)/
  );
  return match ? match[1] : null;
}

function getVimeoId(url = "") {
  const match = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
  return match ? match[1] : null;
}

export default function ManifestoVideo({ page }) {
  const [playing, setPlaying] = useState(false);
  const videoRef = useRef(null);

  const videoUrl = page?.manifestoVideo || "";
  const youtubeId = getYouTubeId(videoUrl);
  const vimeoId = getVimeoId(videoUrl);
  const isYouTube = !!youtubeId;
  const isVimeo = !!vimeoId;
  const isMp4 = /\.mp4($|\?)/i.test(videoUrl);

  const embedUrl = useMemo(() => {
    if (isYouTube) {
      return `https://www.youtube.com/embed/${youtubeId}?autoplay=1&rel=0&playsinline=1`;
    }
    if (isVimeo) {
      return `https://player.vimeo.com/video/${vimeoId}?autoplay=1`;
    }
    return null;
  }, [isYouTube, isVimeo, youtubeId, vimeoId]);

  const playVideo = () => {
    if (isMp4 && videoRef.current) {
      videoRef.current.play();
    }
    setPlaying(true);
  };

  return (
    <section
      className={
        "manifesto" +
        (page?.manifestoImage ? " manifesto--photo" : "") +
        (playing ? " is-playing" : "")
      }
    >
      {page?.manifestoImage && !playing && (
        <img
          className="manifesto__img"
          src={page.manifestoImage}
          alt=""
          loading="lazy"
          decoding="async"
        />
      )}

      {isMp4 && (
        <video
          ref={videoRef}
          className="manifesto__video"
          src={videoUrl}
          poster={page?.manifestoImage || undefined}
          playsInline
          controls={playing}
          preload="metadata"
          onPlay={() => setPlaying(true)}
          onPause={() => setPlaying(false)}
          onEnded={() => setPlaying(false)}
        />
      )}

      {(isYouTube || isVimeo) && playing && embedUrl && (
        <iframe
          className="manifesto__iframe"
          src={embedUrl}
          title={page?.manifestoTitle || "Manifesto video"}
          allow="autoplay; fullscreen; picture-in-picture"
          allowFullScreen
        />
      )}

      {!playing && (
        <div className="container manifesto__content rv">
          <h2 className="display">
            {page?.manifestoTitle || "Manifesto 55 Years of Combiphar"}
          </h2>

          {videoUrl ? (
            <button
              type="button"
              className="manifesto__play"
              onClick={playVideo}
              aria-label="Play video"
            >
              <svg
                width="65"
                height="65"
                viewBox="0 0 65 65"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M32.5 0C14.56 0 0 14.56 0 32.5C0 50.44 14.56 65 32.5 65C50.44 65 65 50.44 65 32.5C65 14.56 50.4725 0 32.5 0ZM42.25 39.7475L32.825 45.175C31.655 45.8575 30.355 46.1825 29.0875 46.1825C27.7875 46.1825 26.52 45.8575 25.35 45.175C23.01 43.81 21.6125 41.405 21.6125 38.675V27.7875C21.6125 25.09 23.01 22.6525 25.35 21.2875C27.69 19.9225 30.485 19.9225 32.8575 21.2875L42.2825 26.715C44.6225 28.08 46.02 30.485 46.02 33.215C46.02 35.945 44.6225 38.3825 42.25 39.7475Z"
                  fill="white"
                />
              </svg>
            </button>
          ) : (
            <button
              type="button"
              className="manifesto__play"
              aria-label="Play video"
              disabled
            >
              <svg
                width="65"
                height="65"
                viewBox="0 0 65 65"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M32.5 0C14.56 0 0 14.56 0 32.5C0 50.44 14.56 65 32.5 65C50.44 65 65 50.44 65 32.5C65 14.56 50.4725 0 32.5 0ZM42.25 39.7475L32.825 45.175C31.655 45.8575 30.355 46.1825 29.0875 46.1825C27.7875 46.1825 26.52 45.8575 25.35 45.175C23.01 43.81 21.6125 41.405 21.6125 38.675V27.7875C21.6125 25.09 23.01 22.6525 25.35 21.2875C27.69 19.9225 30.485 19.9225 32.8575 21.2875L42.2825 26.715C44.6225 28.08 46.02 30.485 46.02 33.215C46.02 35.945 44.6225 38.3825 42.25 39.7475Z"
                  fill="white"
                />
              </svg>
            </button>
          )}

          <span className="manifesto__label">Play Video</span>
        </div>
      )}
    </section>
  );
}
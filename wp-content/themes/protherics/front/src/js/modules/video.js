import $ from 'jquery';

const selector = {
  videoBox: '.js-video',
}

let isSmallScreen = window.matchMedia('(max-width: 1199px)');

const Video = {
  videoBox: null,
  video: null,
  videoSource: null,
  posterURL: '',
  mobileSource: '',
  source: '',
  getVideoType: (src) => {
    const type = src.slice(src.lastIndexOf('.') + 1);
    return type;
  },
  getVideoTemplate: () => {
    const poster = Video.posterURL ? ` poster='${Video.posterURL}'` : '';
    const videoType = Video.source.slice(Video.source.lastIndexOf('.') + 1);

    const sourceURL = `<source src='${Video.source}' type='video/${videoType}'>`;

    return `<video class='c-video-box__video'${poster} autoplay muted loop playsinline data-keepplaying>
      ${sourceURL}
    </video>`;
  },
  handleAppendVideo: () => {
    Video.videoBox.append(Video.getVideoTemplate());

    Video.video = Video.videoBox.find('video')[0];
    Video.videoSource = Video.videoBox.find('source')[0];
  },
  handleChangeVideo: () => {
    let videoType = '';

    Video.video.pause();

    if (isSmallScreen.matches) {
      videoType = Video.getVideoType(Video.mobileSource);
      Video.videoSource.src = Video.mobileSource;
    } else {
      videoType = Video.getVideoType(Video.source);
      Video.videoSource.src = Video.source;
    }

    Video.videoSource.type = `video/${videoType}`;

    Video.video.load();
    Video.video.play();
  },
  init: () => {
    Video.videoBox = $(selector.videoBox);
    Video.posterURL = Video.videoBox.data('poster');
    Video.mobileSource = Video.videoBox.data('mobile-srcs');
    Video.source = Video.videoBox.data('srcs');

    const shouldContinue = !!Video.videoBox && !!Video.posterURL && !!Video.mobileSource && !!Video.source;

    if (!shouldContinue) return;

    Video.handleAppendVideo();

    Video.handleChangeVideo();

    isSmallScreen.onchange = Video.handleChangeVideo;
  }
}

Video.init()

import store from '../store';

export default {
  setCurrentSong(song) {
    store.commit('SET_SONG', song);
  },

  showCoverArt(song) {
    if (!song) {
      return;
    }

    var imgUrl = '';

    if (song.base64) {
      imgUrl = 'data:image/gif;base64,' + song.base64;
    }

    if (song.coverart) {
      imgUrl = song.coverart;
    }

    if (!imgUrl) {
      return;
    }

    var visible = $('.visible-phone:visible');
    var backgroundSize = 'contain';

    if (visible.length > 0) {
      backgroundSize = 'cover';
    }

    $('#dynamicCss').text('#playbackCover.coverImage:after{background:url(' + imgUrl + ') no-repeat 50% 0% fixed;background-size:' + backgroundSize + ';}');
    $('#playbackCover').addClass('coverImage');
  }
};

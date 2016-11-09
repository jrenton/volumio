import musicPlayer from './musicPlayerService';
import store from '../store';

export default {
  addSongs(songs) {
    store.commit('ADD_TO_QUEUE', songs);

    songs.forEach((song, index) => {
      musicPlayer.add(song);
    });
  },
}

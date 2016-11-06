import musicPlayerService from '../services/musicPlayerService';

export default {
  getCurrentSong({ commit }) {
    musicPlayerService.currentSong((song) => {
      commit('SET_SONG', song);
      // currentSongService.showCoverArt(song);
      refreshTimer(song.elapsed,
                   song.time,
                   song.state);
      refreshKnob();
      // initializePlaybackKnob();
      // initializeVolumeKnob();
      
      // musicPlayer.getCoverArt(song.serviceType, function(coverArt) {
      //     currentSongService.showCoverArt(coverArt);
      // });
    });
  },

  getSearchResults({ commit }, searchTerm, searchType, serviceType) {
    musicPlayerService.search(searchTerm, searchType, serviceType, (results) => {
      commit('SET_SEARCH_RESULTS', results);
    });
  },

  getQueue({ commit }) {
    musicPlayerService.getQueue().then((queue) => {
      commit('SET_QUEUE', queue.tracks);
    });
  },

  getPlaylists({ commit }, name) {
    musicPlayerService.getPlaylists(name).then((playlists) => {
       commit('SET_PLAYLISTS', playlists);
    });
  },

  getPlaylist({ commit }, { id, name }) {
    musicPlayer.getPlaylist(id, name)
               .then((playlist) => {
      commit('SET_PLAYLIST', playlist);
    });
  },

  getServices({ commit }) {
    musicPlayerService.getServices((services) => {
      commit('SET_SERVICES', services);
    });
  },
};
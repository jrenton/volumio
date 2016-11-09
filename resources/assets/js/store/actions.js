import musicPlayerService from '../services/musicPlayerService';
import commandService from '../services/sendCommandService';

export default {
  getCurrentSong({ commit }) {
    musicPlayerService.currentSong((song) => {
      commit('SET_SONG', song);
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
    musicPlayerService.getPlaylist(id, name)
    .then((playlist) => {
      commit('SET_PLAYLIST', playlist);
    });
  },

  getServices({ commit }) {
    musicPlayerService.getServices((services) => {
      commit('SET_SERVICES', services);
    });
  },

  openDirectory({ commit }, directory) {
    return new Promise((resolve, reject) => {
      commandService.send('filepath', directory, (data) => {
        commit('SET_DIRECTORY', { path: directory, data });
        resolve();
      });
    });
  },

  openPlaylist({ commit }, { playlist, serviceType }) {
    musicPlayerService.getPlaylist(playlist.id, serviceType)
    .then((playlist) => {
      commit('SET_PLAYLIST', playlist);
    });
  },
};

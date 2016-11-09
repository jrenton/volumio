import Vue from 'vue';
import Vuex from 'vuex';
import createLogger from 'vuex/dist/logger';

import actions from './actions';

Vue.use(Vuex);

const currentState = window.GUI = {
  MpdState: 0,
  SpopState: 0,
  cmd: 'status',
  playlist: {
    songs: []
  },
  repeat: false,
  consume: false,
  random: false,
  single: false,
  currentsong: {
    id: "",
    artist: "",
    title: "",
    state: "",
    album: "",
    type: "",
    time: 0,
    elapsed: 0,
    volume: 100,
    percentcomplete: 0,
    rating: ""
  },
  currentknob: null,
  currentpath: '',
  halt: 0,
  volume: null,
  currentDBpos: new Array(0,0,0,0,0,0,0,0,0,0,0),
  DBentry: new Array('', '', '', '', '', ''), // path, x, y, title, artist, album
  visibility: 'visible',
  DBupdate: 0,
  browse: {
    currentView: "default",
    isLibrary: false,
    files: [],
    directories: [],
    mpdDirectories: [],
    spotifyTracks: [],
    spotifyDirectories: [],
    pandoraSongs: [],
    pandoraDirectories: [],
  },
  library: {
    showLibrary: false,
  },
  queue: {
    songs: [],
  },
  searchResults: [],
  playlists: [],
};

const mutations = {
  SET_SEARCH_RESULTS(state, searchResults) {
    state.searchResults = searchResults;
  },

  SET_SONG_STATE(state, song) {
    if (typeof song !== 'object') {
      return;
    }

    const songState = getSongState(song);

    if (song.id) {
      state.currentsong.id = song.id;
    }

    if (song.artist) {
      state.currentsong.artist = song.artist;
    }

    if (song.album) {
      state.currentsong.album = song.album;
    }

    if (song.rating) {
      state.currentsong.rating = song.rating;
    }

    if (song.title) {
      state.currentsong.title = song.title;
    }

    if (song.volume) {
      state.currentsong.volume = song.volume;
    }

    if (songState) {
      state.currentsong.state = songState;
    }

    if (songState
        && (state.currentsong.type == song.serviceType
            && song.serviceType == 'Pandora' || songState != 'stopped')) {
      state.currentsong.state = songState;

      if (typeof song.elapsed != "undefined"
          && typeof song.time != "undefined") {
        state.currentsong.elapsed = parseFloat(song.elapsed);
        state.currentsong.time = parseFloat(song.time);
      }
    }

    if (song.serviceType && songState != 'stopped') {
      state.currentsong.type = song.serviceType;
    }

    // Reset the song to trigger state changes.
    state.currentsong = Object.assign({}, state.currentsong);
  },

  SET_SONG(state, song) {
    state.currentsong = {};
    state.currentsong.id = song.id;
    state.currentsong.artist = song.artist;
    state.currentsong.title = song.title;
    state.currentsong.album = song.album;
    state.currentsong.type = song.serviceType;
    state.currentsong.state = getSongState(song);
    state.currentsong.time = song.time;
    state.currentsong.elapsed = song.elapsed;
  },

  SET_QUEUE(state, queue) {
    state.queue = queue;
  },

  SET_PLAYLISTS(state, playlists) {
    state.playlists = playlists;
  },

  SET_SPOTIFY_PLAYLISTS(state, playlists) {
    state.browse.spotifyPlaylists = playlists;
  },

  SET_SERVICES(state, services) {
    state.browse.directories = services;
  },

  SET_PLAYLIST(state, playlist) {
    state.playlist = playlist;
  },

  SET_DIRECTORY(state, { path, data }) {
    if (path) {
      state.currentpath = path;
    }

    state.browse.files = [];
    state.browse.mpdDirectories = [];
    state.browse.spotifyTracks = [];
    state.browse.spotifyDirectories = [];
    state.browse.directories = [];
    state.browse.pandoraDirectories = [];
    state.browse.pandoraSongs = [];
    state.browse.isLibrary = false;

  	// if (!keyword || path == '') {
    //   if (library && library.isEnabled && !library.displayAsTab) {
    //     GUI.browse.isLibrary = true;
    //   }
    // }

  	for (var i = 0; i < data.length; i++) {
      var dataItem = data[i];
      if (dataItem.type == 'MpdFile'
          && dataItem.serviceType == 'Mpd') {
        state.browse.files.push(dataItem);
      }
      else if (dataItem.type == 'MpdDirectory'
               && dataItem.serviceType == "Mpd")  {
        state.browse.mpdDirectories.push(dataItem);
      }
      else if (dataItem.type == 'SpopTrack'
               && dataItem.serviceType == 'Spotify') {
        state.browse.spotifyTracks.push(dataItem);
      }
      else if (dataItem.type == 'playlist'
               && dataItem.serviceType == 'Spotify') {
        state.browse.spotifyDirectories.push(dataItem);
      }
      else if (dataItem.type == 'PandoraDirectory'
               || dataItem.type == 'PandoraStation'
               && dataItem.serviceType == 'Pandora') {
        state.browse.pandoraDirectories.push(dataItem);
      }
      else if (dataItem.type == 'Directory') {
        state.browse.directories.push(dataItem);
      }
  	}
  },

  SET_PLAYLIST(state, playlist) {
    state.playlist = playlist;
  },

  ADD_TO_QUEUE(state, songs) {
    state.queue.songs.concat(songs);
  },
};

function getSongState(song) {
  let songState = song.state || song.status;

  switch (songState) {
    case 'play':
      songState = 'playing';
      break;
    case 'pause':
      songState = 'paused';
      break;
    case 'stop':
      songState = 'stopped';
      break;
  }

  return songState;
}

export default new Vuex.Store({
  state: currentState,
  mutations,
  plugins: [createLogger()],
  actions,
  // plugins: (process.env !== 'Release') ? [createLogger()] : [],
});

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

  SET_SONG(state, song) {
    state.currentsong.artist = song.artist;
    state.currentsong.title = song.title;
    state.currentsong.album = song.album;
    state.currentsong.type = song.serviceType;
    state.currentsong.state = song.state || song.status;
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
};

export default new Vuex.Store({
  state: currentState,
  mutations,
  plugins: [createLogger()],
  actions,
  // plugins: (process.env !== 'Release') ? [createLogger()] : [],
});
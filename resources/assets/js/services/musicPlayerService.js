import ajaxUtils from './ajaxUtilsService';
import currentSongService from './currentSongService';

export default {
  uri: "player",
  send(command, data, callback) {
    //window.volumio.conn.send(command);
    if (!data) {
      data = {};
    }
    data.cmd = command;
    ajaxUtils.post(this.uri, data, function(data) {
      if (typeof callback === "function") {
        callback(data);
      }
    });
  },

  sendPromise(command, data) {
    data.cmd = command;
    return new Promise((resolve, reject) => {
      ajaxUtils.post(this.uri, data, (res) => {
        resolve(res);
      }, (a) => {
        reject(a);
      });
    });
  },

  add(song, callback) {
    this.send("add", { song: song, serviceType: song.serviceType }, callback);
  },

  play(song, serviceType, callback) {
    this.send("play", { song: song, serviceType: serviceType }, callback);
    currentSongService.showCoverArt(song);
  },

  pause(serviceType, callback) {
    this.send("pause", { serviceType: serviceType }, callback);
  },

  stop(serviceType, callback) {
    this.send("stop", { serviceType: serviceType }, callback);
  },

  next(serviceType, callback) {
    this.send("next", { serviceType: serviceType }, callback);
  },

  previous(serviceType, callback) {
    this.send("previous", { serviceType: serviceType }, callback);
  },

  getPlaylists(serviceType, callback) {
    return this.sendPromise("getPlaylists", { serviceType: serviceType });
  },

  getPlaylist(id, serviceType, callback) {
    return this.sendPromise("getPlaylist", { playlist: { id: id }, serviceType: serviceType });
  },

  playPlaylist(playlist, serviceType, callback) {
    this.send("playPlaylist", { playlist: playlist, serviceType: serviceType }, callback);
  },

  addPlaylist(playlist, serviceType, callback) {
    this.send("addPlaylist", { playlist: { name: playlist }, serviceType: serviceType }, callback);
  },

  rateUp(song, serviceType, callback) {
    this.send("rateUp", { song: song, serviceType: serviceType }, callback);
  },

  rateDown(song, serviceType, callback) {
    this.send("rateDown", { song: song, serviceType: serviceType }, callback);
  },

  single(serviceType, callback) {
    this.send("single", { serviceType: serviceType }, callback);
  },

  repeat(serviceType, callback) {
    this.send("repeat", { serviceType: serviceType }, callback);
  },

  consume(serviceType, callback) {
    this.send("consume", { serviceType: serviceType }, callback);
  },

  random(serviceType, callback) {
    this.send("random", { serviceType: serviceType }, callback);
  },

  removeFromQueue(song, serviceType, callback) {
    this.send("removeQueue", { song: song, serviceType: serviceType }, callback);
  },

  removeFromPlaylist(song, serviceType, callback) {
    this.send("removePlaylist", { song: song, serviceType: serviceType }, callback);
  },

  openService(serviceType) {
    return this.sendPromise("openService", { serviceType: serviceType });
  },

  search(searchTerm, searchType, serviceType, callback) {
    this.send("search", { query: searchTerm, searchType: searchType, serviceType: serviceType}, callback);
  },

  currentSong(callback) {
    this.send("currentSong", null, callback);
  },

  getServices(callback) {
    this.send("getServices", null, callback);
  },

  getCoverArt(serviceType, callback) {
    return this.sendPromise("image", { serviceType: serviceType });
  },

  getQueue(callback) {
    return this.sendPromise("getQueue", {});
  },

  removeQueue(song, serviceType) {
    return this.sendPromise("removeQueue", { song: song, serviceType: serviceType });
  },
}

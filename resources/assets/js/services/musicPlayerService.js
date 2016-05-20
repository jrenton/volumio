var ajaxUtils = require("./ajaxUtilsService");
var currentSongService = require("../services/currentSongService");

module.exports = {
    uri: "player",
    send: function(command, data, callback) {
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
    sendPromise: function(command, data) {
        var _self = this;
        data.cmd = command;
        return new Promise(function(resolve, reject) {
            ajaxUtils.post(_self.uri, data, function(data) {
                resolve(data);
            }, function(a) {
                reject(a);            
            });
        });
    },
    add: function(song, callback) {
        this.send("add", { song: song, serviceType: song.serviceType }, callback);
    },
    play: function(song, serviceType, callback) {
        this.send("play", { song: song, serviceType: serviceType }, callback);
        currentSongService.showCoverArt(song);
    },
    pause: function(serviceType, callback) {
        this.send("pause", { serviceType: serviceType }, callback);
    },
    stop: function(serviceType, callback) {
        this.send("stop", { serviceType: serviceType }, callback);
    },
    next: function(serviceType, callback) {
        this.send("next", { serviceType: serviceType }, callback);
    },
    previous: function(serviceType, callback) {
        this.send("previous", { serviceType: serviceType }, callback);
    },
    getPlaylists: function(serviceType, callback) {
        return this.sendPromise("getPlaylists", { serviceType: serviceType });
    },
    getPlaylist: function(id, serviceType, callback) {
        return this.sendPromise("getPlaylist", { playlist: { id: id }, serviceType: serviceType });
    },
    playPlaylist: function(playlist, serviceType, callback) {
        this.send("playPlaylist", { playlist: playlist, serviceType: serviceType }, callback);
    },
    addPlaylist: function(playlist, serviceType, callback) {
        this.send("addPlaylist", { playlist: { name: playlist }, serviceType: serviceType }, callback);
    },
    rateUp: function(song, serviceType, callback) {
        this.send("rateUp", { song: song, serviceType: serviceType }, callback);
    },
    rateDown: function(song, serviceType, callback) {
        this.send("rateDown", { song: song, serviceType: serviceType }, callback);
    },
    single: function(serviceType, callback) {
        this.send("single", { serviceType: serviceType }, callback);
    },
    repeat: function(serviceType, callback) {
        this.send("repeat", { serviceType: serviceType }, callback);
    },
    consume: function(serviceType, callback) {
        this.send("consume", { serviceType: serviceType }, callback);
    },
    random: function(serviceType, callback) {
        this.send("random", { serviceType: serviceType }, callback);
    },
    removeFromQueue: function(song, serviceType, callback) {
        this.send("removeQueue", { song: song, serviceType: serviceType }, callback);
    },
    removeFromPlaylist: function(song, serviceType, callback) {
        this.send("removePlaylist", { song: song, serviceType: serviceType }, callback);
    },
    openService: function(serviceType) {
        return this.sendPromise("openService", { serviceType: serviceType });
    },
    search: function(searchTerm, searchType, serviceType, callback) {
        this.send("search", { query: searchTerm, searchType: searchType, serviceType: serviceType}, callback);
    },
    currentSong: function(callback) {
        this.send("currentSong", null, callback);
    },
    getServices: function(callback) {
        this.send("getServices", null, callback);
    },
    getCoverArt: function(serviceType, callback) {
        return this.sendPromise("image", { serviceType: serviceType });
    }
}
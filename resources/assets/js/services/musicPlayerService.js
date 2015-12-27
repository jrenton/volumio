var ajaxUtils = require("./ajaxUtilsService");

module.exports = {
    uri: "player",
    send: function(command, song, serviceType, callback) {
        //window.volumio.conn.send(command);
        ajaxUtils.post(this.uri, { cmd: command, song: song, serviceType: serviceType }, function(data) {
            if (typeof callback === "function") {
                callback(data);
            }
        });
    },
    add: function(song, serviceType, callback) {
        this.send("add", song, serviceType, callback);
    },
    play: function(song, serviceType, callback) {
        this.send("play", song, serviceType, callback);
    },
    pause: function(serviceType, callback) {
        this.send("pause", null, serviceType, callback);
    },
    stop: function(serviceType, callback) {
        this.send("stop", null, serviceType, callback);
    },
    getPlaylists: function(serviceType, callback) {
        this.send("getPlaylists", null, serviceType, callback);
    }
}
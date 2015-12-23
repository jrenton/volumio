var ajaxUtils = require("./ajaxUtilsService");

module.exports = {
    uri: "player",
    add: function(song, callback) {    
        AjaxUtils.post(this.uri, { cmd: "add", song: song }, function(data) {
            if (typeof callback === "function") {
                callback(data);
            }
        });
    },
    play: function(song, callback) {    
        AjaxUtils.post(this.uri, { cmd: "play", song: song }, function(data) {
            if (typeof callback === "function") {
                callback(data);
            }
        });
    },
    pause: function(song, callback) {
        AjaxUtils.post(this.uri, { cmd: "pause", song: song }, function(data) {
            if (typeof callback === "function") {
                callback(data);
            }
        });
    },
    stop: function(song, callback) {
        AjaxUtils.post(this.uri, { cmd: "stop", song: song }, function(data) {
            if (typeof callback === "function") {
                callback(data);
            }
        });
    }
}
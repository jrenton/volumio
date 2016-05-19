var store = require('../store');
var musicPlayer = require("../services/musicPlayerService");
var queue = require("../services/queueService");
var router = window.volumio.router;

module.exports = {
    template: require('./search.html'),
	data: function() {
        return store.state;
	},
	methods: {
        play: function (song) {
            var _self = this;
            musicPlayer.play(song, song.serviceType, function(data) {                
                router.go({ name: "playback" });
                
                queue.addSongs(_self.playlist.songs);
        
                getPlaylist();
            });
	    }
    }
}
var store = require('../store');

module.exports = {
	template: require('./playlist.html'),
	data: function() {
        return {
            playlist: store.state.playlist,
            queue: store.state.queue
        };
	},
	methods: {
	    playSpotifySong: function (song) {
	      sendCommand("spop-goto", { "path": song.index });
	    },
	    removeSpotifySong: function (song) {
	    	sendCommand("spop-qrm", { "path": song.index }, function(data) {
	    		console.log(data);
	    		getPlaylist();
	    	});
	    },
        playMpdSong: function (song) {
            sendCmd("play " + song.index);
        },
        removeMpdSong: function (song) {
            sendCmd("trackremove&songid=" + song.index);
        }
	}
}
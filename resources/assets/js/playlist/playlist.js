var store = require('../store');
var musicPlayer = require('../services/musicPlayerService');

module.exports = {
	template: require('./playlist.html'),
	data: function() {
        return {
            currentSong: store.state.currentSong,
            playlist: store.state.playlist,
            queue: store.state.queue
        };
	},
    ready: function() {
      musicPlayer.getQueue().then(function(queue) {
          console.log('queue!');
          console.log(queue);
         this.queue = queue.tracks; 
      });
    },
	methods: {
	    play: function (song) {
            musicPlayer.play(song, song.serviceType);
	    },
	    removeFromQueue: function (song) {
            musicPlayer.removeQueue(song, song.serviceType);
	    },
        removeFromPlaylist: function (song) {
            musicPlayer.removePlaylist(song, song.serviceType);            
        }
	}
}
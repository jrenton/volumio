var volumio = window.volumio || {};

volumio.playback = Vue.extend({
    template: require('./browse.html'),
	data: function() {
        return volumio.state.browse;
		// return {
		// 	isLibrary: false,
		// 	files: [{ title: "Something" }],
		// 	mpdDirectories: [],
		// 	spotifyTracks: [],
		// 	spotifyDirectories: []
		// }
	},
	methods: {
	    playSong: function (song) {
            sendCommands([
                        { name: 'spop-stop' }, 
                        { name: 'addplay', data: { path: song.file }}
                        ], function(data) {
                gotoPlayback();
            });
            
            //notify('add', song.title);
	    },
        playSpotifyTrack: function (playTrack) {
            sendCommand("spop-uplay", playTrack.uri, function(data) {
                gotoPlayback(playTrack);
                //getPlaylist();
            });
    
            $.each(this.spotifyTracks, function(index, track) {
                var trackUri = track.uri;

                if (trackUri && track.uri != playTrack.uri) {
                    sendCommand("spop-uadd", { path: trackUri });
                }
            });
    
            getPlaylist();
        },

        getFileName: function (file) {
            var title = file.title;
            
            if (!title) {
                title = file.Name;
                
                if (!title) {
                    var lastIndex = file.file.lastIndexOf('/') + 1;
                    title = file.file.substr(lastIndex, file.file.length - lastIndex - 4);
                }
            }
            
            return title;
        },
        getAlbumArtist: function (file) {
            var albumArtist = "";
            
            if (file.artist && file.album) {
                albumArtist = file.artist + " - " + file.album;
            } else if(file.artist) {
                albumArtist = file.artist;
            }
            
            return albumArtist;
        }
	}
});
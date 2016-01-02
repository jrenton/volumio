var store = require("../store");
var musicPlayer = require("../services/musicPlayerService");
var queue = require("../services/queueService");
var _ = require("lodash");

module.exports = {
    template: require("./browse.html"),
	data: function() {
        return store.state.browse;
	},
	methods: {
        openPandora: function (station) {
            console.log(station);
            if (station.Type == "PandoraDirectory") {
                musicPlayer.getPlaylists("Pandora", function(data) {
                    populateDB(data);
                });
            } else if (station.Type == "PandoraStation") {
                musicPlayer.playPlaylist(station, "Pandora", function(data) {
                    
                    //populateDB(data);
                    window.volumio.router.go("playback");
                });
            }
        },
	    play: function (song) {
            var _self = this;
            console.log("play this song");
            console.log(song);
            musicPlayer.play(song, song.serviceType, function(data) {                
                window.volumio.router.go("playback");
                
                queue.addSongs(_.filter(_self.spotifyTracks, function(track) {
                    return track.uri != song.uri;
                }));
        
                getPlaylist();
            });
            //notify('add', song.title);
	    },
        add: function (song) {
            musicPlayer.add(song);
        },
        searchTitle: function (song) {
            //musicPlayer.add(song);
        },
        searchArtist: function (song) {
            //musicPlayer.add(song);
        },
        // playSpotifyTrack: function (playTrack) {
        //     sendCommand("spop-uplay", playTrack.uri, function(data) {
        //         gotoPlayback(playTrack);
        //         //getPlaylist();
        //     });
    
        //     $.each(this.spotifyTracks, function(index, track) {
        //         var trackUri = track.uri;

        //         if (trackUri && track.uri != playTrack.uri) {
        //             sendCommand("spop-uadd", { path: trackUri });
        //         }
        //     });
    
        //     getPlaylist();
        // },
        openDirectory: function (dir) {
            getDB('filepath', dir.directory, 'file', 0);
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
}
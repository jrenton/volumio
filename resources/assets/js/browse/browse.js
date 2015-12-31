var store = require('../store');
var musicPlayer = require("../services/musicPlayerService");

module.exports = {
    template: require('./browse.html'),
	data: function() {
        return store.state.browse;
	},
	methods: {
        openPandora: function (station) {
            console.log(station);
            if (station.Type == "PandoraDirectory") {
                musicPlayer.getPlaylists("Pandora", function(data) {
                    populateDB(data);
                    //window.volumio.router.go("playback");
                });
            } else if (station.Type == "PandoraStation") {
                musicPlayer.playPlaylist(station, "Pandora", function(data) {
                    
                    //populateDB(data);
                    window.volumio.router.go("playback");
                });
            }
        },
	    play: function (song) {
            musicPlayer.play(song, song.ServiceType, function(data) {
                window.GUI.currentsong.artist = song.Artist;
                window.GUI.currentsong.title = song.Title;
                window.GUI.currentsong.album = song.Album;
                window.GUI.currentsong.state = "play";
                window.volumio.router.go("playback");
            });
            // sendCommands([
            //             { name: 'spop-stop' }, 
            //             { name: 'addplay', data: { path: song.file }}
            //             ], function(data) {
            //     gotoPlayback();
            // });
            
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
        //     sendCommand("spop-uplay", playTrack.SpopTrackUri, function(data) {
        //         gotoPlayback(playTrack);
        //         //getPlaylist();
        //     });
    
        //     $.each(this.spotifyTracks, function(index, track) {
        //         var trackUri = track.SpopTrackUri;

        //         if (trackUri && track.SpopTrackUri != playTrack.SpopTrackUri) {
        //             sendCommand("spop-uadd", { path: trackUri });
        //         }
        //     });
    
        //     getPlaylist();
        // },
        openDirectory: function (dir) {
            getDB('filepath', dir.directory, 'file', 0);
        },
        getFileName: function (file) {
            var title = file.Title;
            
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
            
            if (file.Artist && file.Album) {
                albumArtist = file.Artist + " - " + file.Album;
            } else if(file.Artist) {
                albumArtist = file.Artist;
            }
            
            return albumArtist;
        }
	}
}
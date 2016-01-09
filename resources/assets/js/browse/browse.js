var store = require("../store");
var musicPlayer = require("../services/musicPlayerService");
var queue = require("../services/queueService");
var _ = require("lodash");
var router = window.volumio.router;

module.exports = {
    template: require("./browse.html"),
	data: function() {
        return store.state.browse;
	},
    ready: function() {
        musicPlayer.getServices(function(services) {
           store.state.browse.directories = services; 
        });
    },
	methods: {
        openDirectory: function(dir) {
            var serviceType = dir.serviceType.toLowerCase();
            switch (dir.type) {
                case "Directory":
                    router.go({ 
                        name: "playlists",
                        params: {
                            name: serviceType
                        }
                    });
                    break;
            }
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
	},
    components: {
        "default": {
            data: function() {
                return store.state.browse
            },
            template: require("./directories.html")
        },
        "spotify": {
            template: require("./spotify/spotify.html"),
            activate: function (done) {
                var _self = this;
                
                musicPlayer.openService("spotify").then(function(data) {
                    _self.spotifyPlaylists = data;
                    done(); 
                });
            }
        },
        "pandora": {
            template: require("./pandora/pandora.html"),
            activate: function (done) {
                // load data specific to pandora
                done();
            }
        }
    }
}
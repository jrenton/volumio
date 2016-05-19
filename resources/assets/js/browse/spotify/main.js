var store = require("../../store");
var musicPlayer = require("../../services/musicPlayerService");
var queue = require("../../services/queueService");
var router = window.volumio.router;

module.exports = {
    template: require("./main.html"),
    data: function() {
        return { 
            spotifyPlaylists: [],
            spotifyTracks : []
        };
    },
    methods: {
        openDirectory: function(dir) {
            window.volumio.router.go({ name: dir.serviceType.toLowerCase() });
        }
    },
    route: {
        data: function() {
            return musicPlayer.openService("spotify").then(function(data) {
                console.log(data);
                return { 
                    spotifyPlaylists: data
                };   
            });
        }
    }
}
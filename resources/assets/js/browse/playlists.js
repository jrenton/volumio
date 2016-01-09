var store = require("../store");
var musicPlayer = require("../services/musicPlayerService");
var queue = require("../services/queueService");
var router = window.volumio.router;

module.exports = {
    template: require("./playlists.html"),
    data: function() {
        return { 
            playlists: []
        }; 
    },
    methods: {
        addPlaylist: function(playlist) {
          musicPlayer.addPlaylist(playlist, this.$route.params.name);
          
          return false;
        },
        openDirectory: function(dir) {
            console.log("open this directory");
            console.log(dir);
            if (dir.type === "folder") {
                
            } else if (dir.type === "playlist") {
                window.volumio.router.go({ 
                    name: "viewplaylist", 
                    params : {
                        name: dir.serviceType.toLowerCase(),
                        id: dir.id
                    }
                });
            } else if (dir.type === "RadioStation") {
                musicPlayer.playPlaylist(dir, dir.serviceType, function() {
                   window.volumio.router.go({ name: "playback" }); 
                });
            }
        }
    },
    route: {
        data: function(transition) {
            return musicPlayer.getPlaylists(this.$route.params.name).then(function(data) {
                return { 
                    playlists: data
                };   
            });
        }
    }
}
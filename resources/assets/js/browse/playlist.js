var store = require("../store");
var musicPlayer = require("../services/musicPlayerService");
var queue = require("../services/queueService");
var router = window.volumio.router;

module.exports = {
    template: require("./playlist.html"),
    data: function() {
        return { 
            playlist: {}
        }; 
    },
    methods: {
        play: function (song) {
            var _self = this;
            musicPlayer.play(song, song.serviceType, function(data) {                
                router.go({ name: "playback" });
                
                queue.addSongs(_self.playlist.songs);
        
                getPlaylist();
            });
            //notify('add', song.title);
	    },
        add: function (song) {
            musicPlayer.add(song);
        },
        search: function (searchTerm, type, serviceType) {
            musicPlayer.search(searchTerm, type, serviceType);
        }
    },
    route: {
        data: function(transition) {
            return musicPlayer.getPlaylist(this.$route.params.id, this.$route.params.name).then(function(data) {
                console.log(data);
                return { 
                    playlist: data
                };   
            });
        }
    }
}
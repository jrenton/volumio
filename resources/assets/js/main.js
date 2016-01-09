var Vue = require("vue");
var VueRouter = require("vue-router");
var store = require("./store");
var musicPlayer = require("./services/musicPlayerService");
var currentSongService = require("./services/currentSongService");

Vue.use(VueRouter);

Vue.config.debug = true;

window.volumio = window.volumio || {};

window.volumio.router = new VueRouter({
    linkActiveClass: "active"
});

window.volumio.router.map({
    '/': {
        name: "default",
        component: require('./playback/playback')
    },
    '/browse': {
        name: "browse",
        component: require('./browse/browse'),
        subRoutes: {
            '/': {
                component: {
                    template: require("./browse/directories.html")                    
                }
            },
            '/:name': {
                name: "browseservice",
                component: {
                    template: require("./browse/directories.html")                    
                }
            },
            '/:name/playlists': {
                name: "playlists",
                component: require('./browse/playlists')
            },
            '/:name/playlists/:id': {
                name: "viewplaylist",
                component: require('./browse/playlist')
            }
        }
    },
    // '/library': {
    //     component: libraryView
    // },
    '/playlist': {
        name: "playlist",
        component: require('./playlist/playlist')
    },
    '/playback': {
        name: "playback",
        component: require('./playback/playback')
    },
    '/search': {
        name: "search",
        component: require('./search/search')
    }
});

var App = Vue.extend({
    data: function() {
        return store.state;
    },
    ready: function() {
        musicPlayer.currentSong(function(song) {
            currentSongService.setCurrentSong(song);
            currentSongService.showCoverArt(song);
        });
    },
    methods: {
        goBack: function() {
            this.$route.router.go(window.history.back());
        },
        search: function(searchTerm) {
            musicPlayer.search(searchTerm, "", "spotify", function(data) {
                console.log("search");
                console.log(data);
                store.state.searchResults = data;
            });
            window.volumio.router.go({ name: "search" });
            
            return false;
        }
    }
});
// Start the App
window.volumio.router.start(App, '#app');

$(function() {
    // INITIALIZATION
    // ----------------------------------------------------------------------------------------------------
    // first connection with MPD and SPOP daemons
    // backendRequest(window.GUI);
    // backendRequestPandora(window.GUI);
	// backendRequestSpop(window.GUI);

    // getDB('filepath', GUI.currentpath, 'file');
    // $.pnotify.defaults.history = false;
    // getPlaylist();

    // hide "connecting" layer
    // if (GUI.state != 'disconnected') {
    //     $('#loader').hide();
    // }
});
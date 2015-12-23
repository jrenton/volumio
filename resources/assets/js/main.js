var Vue = require("vue");
var VueRouter = require("vue-router");
var store = require("./store");
//var appView = require('./app.js');
// var browseView = require('./browse.js');
// var libraryView = require('./library.js');
//var view404 = require("./views/404.vue");
Vue.use(VueRouter);

Vue.config.debug = true;

var volumio = window.volumio || {};

volumio.router = new VueRouter();

volumio.router.map({
    '/': {
        name: "default",
        component: require('./playback/playback')
    },
    '/browse': {
        name: "browse",
        component: require('./browse/browse')
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
    }
});

var App = Vue.extend({
    data: function() {
        return store.state;
    }
});
// Start the App
volumio.router.start(App, '#app');

$(function() {
    // INITIALIZATION
    // ----------------------------------------------------------------------------------------------------
    // first connection with MPD and SPOP daemons
    backendRequest(window.GUI);
	backendRequestSpop(window.GUI);

    getDB('filepath', GUI.currentpath, 'file');
    $.pnotify.defaults.history = false;
    //getPlaylist();

    // hide "connecting" layer
    if (GUI.state != 'disconnected') {
        $('#loader').hide();
    } 
});
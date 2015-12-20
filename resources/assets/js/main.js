var Vue = require("vue");
var VueRouter = require("vue-router");
// var store = require("./store");
//var appView = require('./app.js');
// var browseView = require('./browse.js');
// var libraryView = require('./library.js');
//var view404 = require("./views/404.vue");
Vue.use(VueRouter);

Vue.config.debug = true;

var router = new VueRouter();

router.map({
    '/': {
        component: require('./playback/playback')
    },
    '/browse': {
        component: require('./browse/browse.js')
    },
    // '/library': {
    //     component: libraryView
    // },
    '/playlist': {
        component: require('./playlist/playlist')
    },
    '/playback': {
        component: require('./playback/playback')
    }
})

var App = Vue.extend({});
// Start the App
router.start(App, '#app');

$(function() {
    // INITIALIZATION
    // ----------------------------------------------------------------------------------------------------
    // first connection with MPD and SPOP daemons
    backendRequest(window.GUI);
	backendRequestSpop(window.GUI);

    // first GUI update
    updateGUI(window.GUI.MpdState);
    getDB('filepath', GUI.currentpath, 'file');
    $.pnotify.defaults.history = false;
    getPlaylist();

    // hide "connecting" layer
    if (GUI.state != 'disconnected') {
        $('#loader').hide();
    } 
});
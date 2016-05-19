var store = require("../../store");
var musicPlayer = require("../../services/musicPlayerService");
var queue = require("../../services/queueService");
var router = window.volumio.router;

module.exports = {
    template: require("./mpd.html"),
	data: function() {
        //
	}
}
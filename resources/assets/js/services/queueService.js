var musicPlayer = require("./musicPlayerService");
var store = require("../store");

module.exports = {
    addSongs: function(songs) {
        $.each(songs, function(index, song) {
            musicPlayer.add(song);
            store.state.queue.songs.push(song);
        });
    }
}
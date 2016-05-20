var store = require("../store");

module.exports = {
    setCurrentSong: function(song) {
        console.log('set current song');
        console.log(song);
        store.state.currentsong.artist = song.artist;
        store.state.currentsong.title = song.title;
        store.state.currentsong.album = song.album;
        store.state.currentsong.type = song.serviceType;
        store.state.currentsong.state = song.state;
        store.state.currentsong.time = song.time;
        store.state.currentsong.elapsed = song.elapsed;
    },
    showCoverArt: function(song) {
        if (!song) {
            return;
        }
        
        var imgUrl = "";
    
        if (song.base64) {
            imgUrl = "data:image/gif;base64," + song.base64;
        }
        
        if (song.coverart) {
            imgUrl = song.coverart;
        }
        
        if (!imgUrl) {
            return;
        }
        
        var visible = $(".visible-phone:visible");
        var backgroundSize = "contain";
        
        if(visible.length > 0) {
            backgroundSize = "cover";
        }
        
        $("#dynamicCss").text("#playbackCover.coverImage:after{background:url(" + imgUrl + ") no-repeat 50% 0% fixed;background-size:" + backgroundSize + ";}");
        $("#playbackCover").addClass("coverImage");
    }
};
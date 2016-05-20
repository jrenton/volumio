var store = require('../store');
var musicPlayer = require("../services/musicPlayerService");
var timeControl = require("../services/timeControl");
var currentSongService = require("../services/currentSongService");

module.exports = {
    template: require('./playback.html'),
	data: function() {
        return { 
            song: store.state.currentsong
        };
	},
    ready: function() {
        musicPlayer.currentSong(function(song) {
            currentSongService.setCurrentSong(song);
            currentSongService.showCoverArt(song);
            timeControl.refreshTimer();
            timeControl.refreshKnob();
            initializePlaybackKnob();
            initializeVolumeKnob();
            
            musicPlayer.getCoverArt(song.serviceType, function(coverArt) {
                currentSongService.showCoverArt(coverArt);
            });
        });
    },
	methods: {
	    playPause: function () {
            var cmd = '';
            if (this.song.state == 'play') {
                cmd = 'pause';
                $('#countdown-display').countdown('pause');
            } else if (this.song.state == 'pause') {
                cmd = 'play';
                $('#countdown-display').countdown('resume');
            } else if (this.song.state == 'stop') {
                cmd = 'play';
                $('#countdown-display').countdown({since: 0, compact: true, format: 'MS'});
            }
            var serviceType = window.GUI.currentsong.type;

            window.clearInterval(GUI.currentKnob);
            switch(cmd) {
                case "play":
                    musicPlayer.play(null, serviceType, function(data) {
                    });
                    break;
                case "pause":
                    musicPlayer.pause(serviceType, function(data) {
                    });
                    break;
            }
            //sendCmd(cmd);
            //sendCommand("spop-goto", { "path": this.song.index });
	    },
	    nav: function (direction) {
	    	GUI.halt = 1;
			$('#countdown-display').countdown('pause');
			window.clearInterval(GUI.currentKnob);
            var serviceType = window.GUI.currentsong.type;
            switch (direction) {
                case "next":
                    musicPlayer.next(serviceType, function(data) {
                    });
                    break;
                case "previous":
                    musicPlayer.previous(serviceType, function(data) {
                        
                    });
                    break;
            }
	    },
        rateUp: function () {
            musicPlayer.rateUp(this.song, this.song.type);
        },
        rateDown: function () {
            musicPlayer.rateDown(this.song, this.song.type);
        },
        random: function () {
            musicPlayer.random(this.song.type);
        },
        single: function () {
            musicPlayer.single(this.song.type);
        },
        consume: function () {
            musicPlayer.consume(this.song.type);
        },
        repeat: function () {
            musicPlayer.repeat(this.song.type);
        }
	}
};

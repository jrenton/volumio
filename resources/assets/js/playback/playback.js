var store = require('../store');
var musicPlayer = require("../services/musicPlayerService");
var timeControl = require("../services/timeControl");

module.exports = {
    template: require('./playback.html'),
	data: function() {
        return { 
            song: store.state.currentsong
        };
	},
    ready: function() {
        timeControl.refreshTimer();
        timeControl.refreshKnob();
        initializePlaybackKnob();
        initializeVolumeKnob();
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
            //sendCmd(direction);
	    }
	}
};

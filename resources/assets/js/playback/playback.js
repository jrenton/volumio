//var store = require('../store');

module.exports = {
    template: require('./playback.html'),
	data: function() {
		//return { song: {} };
        return { song: window.GUI.currentsong };
        //return { song: { Artist: "Eminem", Title: "Superman" } };
	},
	methods: {
	    playPause: function () {
            var cmd = '';
            if (GUI.state == 'play') {
                cmd = 'pause';
                $('#countdown-display').countdown('pause');
            } else if (GUI.state == 'pause') {
                cmd = 'play';
                $('#countdown-display').countdown('resume');
            } else if (GUI.state == 'stop') {
                cmd = 'play';
                $('#countdown-display').countdown({since: 0, compact: true, format: 'MS'});
            }

            window.clearInterval(GUI.currentKnob);
            sendCmd(cmd);
            resetState();
            //sendCommand("spop-goto", { "path": this.song.index });
	    },
	    nav: function (direction) {
	    	GUI.halt = 1;
            //console.log("nav bitch");
			$('#countdown-display').countdown('pause');
			window.clearInterval(GUI.currentKnob);
            
            sendCmd(direction);
            //resetState();
	    }
	}
};
